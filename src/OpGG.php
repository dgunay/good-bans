<?php

namespace GoodBans;

use GoodBans\ChampionsDataSource;
use GoodBans\ApiClient;
use RiotAPI\RiotAPI;

/**
 * Gets data from http://op.gg. It can aggregate across different regions.
 * Only gets SR ranked data. 
 */
class OpGG extends ChampionsDataSource
{
  // TODO: replace with enum constants
  /** @var string[] REGION_SUBDOMAINS Valid region subdomains. 'www' is Korea. */
  private const REGION_SUBDOMAINS = [ 'na', 'www', 'jp', 'euw', 'eune', 'oce',
                                      'br', 'las', 'lan', 'ru', 'tr', 'sg', 'id',
                                      'ph', 'tw', 'vn', 'th', ];
  
  /** @var string[] GRAPH_TYPES Gettable stats (win/loss rate, pick/ban rate) */
  private const GRAPH_TYPES = [ 'win', 'lose', 'picked', 'banned' ];

  // TODO: make these selectable
  /** @var string[] PERIODS Date ranges for data to access */
  private const PERIODS = [ 'month', 'week', 'today' ];

  /** @var string[] LEAGUES Ranked leagues with accessible data. '' is all leagues. */
  private const LEAGUES = [ '', 'bronze', 'silver', 'gold', 'platinum',
                            'diamond', 'master', 'challenger' ];

  /** @var int MAP_ID 1 = SR. We only care about Summoner's Rift stats.*/ 
  private const MAP_ID = 1;

  /** @var string QUEUE We only care about ranked stats. */
  private const QUEUE  = 'ranked';

  /** @var string printf() pattern to enable easy mocking of getting data. */
  protected const PATCH_URI_PATTERN = "https://ddragon.leagueoflegends.com/realms/%s.json";

  /** @var string[] $regions Currently selected regions. Default is na/euw/www */
  private $regions = ['na', 'euw', 'www'];

  /**
   * Gets all elos/leagues offered by op.gg (except for all, which is a blank
   * string).
   *
   * @return string[]
   */
  public function getElos(): array {
    return array_filter(self::LEAGUES);
  }

  /**
   * Allows you to select a list of regions to grab data from. By default it
   * will just return data for NA, EUW, and KR.
   *
   * @param GoodBans\ApiClient $client
   * @param string[] ...$regions
   * @throws \UnexpectedValueException for any invalid region string in $regions
   */
  public function __construct(
    ApiClient $client = null, 
    string ...$regions
  ) {    
    parent::__construct($client);

    if (!empty($regions)) {
      if (!$this->isValid(self::REGION_SUBDOMAINS, ...$regions)) { 
        throw new \UnexpectedValueException(
          "Array of region subdomains contains a value not in OpGG::REGION_SUBDOMAINS."
        ); 
      }

      $this->regions = $regions; 
    }
  }

  /**
   * Validates that all of the values are in $valids. 
   *
   * @param array $valids
   * @param array,... ...$values
   * @return boolean
   */
  private function isValid(array $valids, ...$values) : bool {
    foreach ($values as $value) {
      if (!in_array($value, $valids)) {
        return false;
      }
    }

    return true;
  }

  /**
   * Refreshes champion win/ban/pickrate data from http://op.gg. 
   * 
   * It will aggregate stats across every region that the OpGG was constructed 
   * with. Champions that don't have all 3 of win/ban/pick rate data will be excluded
   * entirely (happens sometimes in challenger with low sample sizes). 
   *
   * @param array $elos Array of elos/leagues to get data for. By default it
   * will get all of them.
   * @return array ['elo' => Champion[], ...]
   */
  protected function refreshChampions(array $elos = []) : array {
    $data = [];
    if (empty($elos)) { $elos = $this->getElos(); }
    
    $champs_by_elo = [];
    foreach ($elos as $elo) {
      $separate_stats = [
        'winrates'  => $this->getStats('win', $elo),
        'banrates'  => $this->getStats('banned'), // these don't have elo-specific stats
        'pickrates' => $this->getStats('picked'), // these don't have elo-specific stats
      ];

      // Throw out champions that don't have a presence in all three arrays
      // (this happens because sometimes challenger is missing data)
      $counts = [
        'winrates'  => count($separate_stats['winrates']), 
        'banrates'  => count($separate_stats['banrates']), 
        'pickrates' => count($separate_stats['pickrates'])
      ];
      if (count(array_unique($counts)) !== 1) {
        asort($counts);
  
        // find the key for the odd one out
        $key_of_lowest_count = array_keys($counts)[0];

        // TODO: this is an assumption - that banrates will exist for everything
        foreach ($separate_stats['banrates'] as $name => $etc) {
          if (!array_key_exists($name, $separate_stats[$key_of_lowest_count])) {
            // remove the missing champ from the rest of the stats
            foreach ($separate_stats as $key => $stats) {
              unset($separate_stats[$key][$name]);
            }
          }
        }
      }

      foreach ($separate_stats['winrates'] as $name => $wr) {
        $champs_by_elo[$elo][$name]['winRate'] = $wr;
      }
      foreach ($separate_stats['banrates'] as $name => $br) {
        $champs_by_elo[$elo][$name]['banRate'] = $br;
      }
      foreach ($separate_stats['pickrates'] as $name => $pr) {
        $champs_by_elo[$elo][$name]['pickRate'] = $pr;
      }
    }

    $patch = $this->getPatch();
    $champ_objects = [];
    foreach ($champs_by_elo as $elo => $champs) {
      foreach ($champs as $name => $data) {
        $data['winRate']  = rtrim($data['winRate'],  ' %') / 100;
        $data['pickRate'] = rtrim($data['pickRate'], ' %') / 100;
        $data['banRate']  = rtrim($data['banRate'],  ' %') / 100;
        $champ_objects[$elo][] = new Champion(array_merge(
          [
            'name'      => $name, 
            'elo'       => $elo,
            'playRate'  => $data['pickRate'],
            'patch'     => $patch,
          ], 
          $data
        ));
      }
    }

    return $champ_objects;
  }

  /**
   * Requests the most recent patch from the Riot DataDragon API since op.gg
   * doesn't really do patch numbers, just  
   *
   * @throws \UnexpectedValueException For failing to parse JSON (unlikely)
   * @throws \UnexpectedValueException For failing to regex the patch number
   * @throws \UnexpectedValueException For finding 0 or 2+ patch numbers.
   * @return string
   */
  public function getPatch() : string {
    if ($this->patch) { return $this->patch; }

    $patches = [];
    foreach ($this->regions as $region) {
      if ($region === 'www') { $region = 'kr'; }
      $url  = sprintf(static::PATCH_URI_PATTERN, $region);
      $json = $this->client->get($url);
      $data = json_decode($json, true);
      if (json_last_error() === JSON_ERROR_NONE) {
        $patches[] = $data['v'];
      }
      else {
        throw new \UnexpectedValueException("Failed to get JSON from $url");
      }
    }

    if (count(array_unique($patches)) === 1) {
      preg_match('/^\d+?\.\d+/', $patches[0], $matches);
      if (isset($matches[0])) { return $matches[0]; }
      throw new \UnexpectedValueException("Failed to regex patch number.");
    }

    throw new \UnexpectedValueException("0 or more than 1 patch found.");
  }

  /**
   * Gets the type of stat from the op.gg 'API' (their exposed table endpoint).
   *
   * @param string $type Valid values in OpGG::GRAPH_TYPES.
   * @param string $league Valid vlaues in OpGG::LEAGUES.
   * @param string $period Defaults to 'month'.
   * @return array
   */
  protected function getStats(
    string $type = 'win', 
    string $league = '', 
    string $period = 'month'
  ) {
    if ($league === 'all') { $league = ''; }

    if (!$this->isValid(self::GRAPH_TYPES, $type)) {
      throw new \UnexpectedValueException("Type '$type' is invalid.");
    }
    if (!$this->isValid(self::LEAGUES, $league)) {
      throw new \UnexpectedValueException("League '$league' is invalid.");
    }
    if (!$this->isValid(self::PERIODS, $period)) {
      throw new \UnexpectedValueException("Period '$period' is invalid.");
    } 

    $stats_by_region = [];
    $aggregate_stats = [];
    foreach ($this->regions as $region) {
      $response = $this->client->post(
        "http://{$region}.op.gg/statistics/ajax2/champion/", 
        [
          'type'   => $type,
          'league' => $league,
          'period' => $period,
          'mapId'  => self::MAP_ID,
          'queue'  => self::QUEUE,
        ]
      );

      // parse the response
      $stats = $this->parseAjaxResponse($response);
      $stats_by_region[$region] = $stats;
    }

    $aggregate_stats = [];
    foreach ($stats_by_region as $stats) {
      foreach ($stats as $champ) {
        $aggregate_stats[$champ['name']] = $champ['stat'];
      }
    }

    return $aggregate_stats;
  }

  /**
   * Parses the HTML table from op.gg's exposed data endpoint into a map
   * of champion name => stat, whatever stat may be.
   *
   * @throws \UnexpectedValueException If no value found for name or stat.
   * @throws \Exception If no rows found.
   * @param string $html
   * @return array
   */
  private function parseAjaxResponse(string $html) : array {
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $x = new \DOMXPath($dom);

    $data = [/* 
      [ 'name' => '', 'stat' => '' ],
      ... */
    ];
    $rows = $x->query("//tbody[@class='Content']/tr");
    if ($rows->count()) {
      foreach ($rows as $row) {
        // name
        $name = null;
        $nodelist = $x->query("./td[3]/a", $row);
        if ($nodelist->count()) {
          $name = $nodelist->item(0)->nodeValue;
        }

        // primary stat (wr/lr/pickrate/banrate)
        $stat = null;
        $nodelist = $x->query("./td[4]/span", $row);
        if ($nodelist->count()) {
          $stat = $nodelist->item(0)->nodeValue;
        } 

        if ($name === null || $stat === null) {
          throw new \UnexpectedValueException("No value found for name or statistic");
        }

        $data[] = ['name' => $name, 'stat' => $stat];
      }
    }
    else {
      throw new \Exception('No rows found.');
    }

    return $data;
  }
}