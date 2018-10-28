<?php

namespace GoodBans;

use GoodBans\ChampionsDataSource;
use GoodBans\ApiClient;
use RiotAPI\RiotAPI;

/**
 * TODO: implement requesting by (if possible):
 *  - Elo
 *  - Time period
 *  - Map
 *  - Queue type
 * 
 * TODO: prefix URL with region
 */
class OpGG extends ChampionsDataSource
{
  // www is korea
  private const REGION_SUBDOMAINS = [ 'na', 'www', 'jp', 'euw', 'eune', 'oce',
                                      'br', 'las', 'lan', 'ru', 'tr', 'sg', 'id',
                                      'ph', 'tw', 'vn', 'th', ];
                                      
  private const GRAPH_TYPES = [ 'win', 'lose', 'picked', 'banned' ];

  private const PERIODS = [ 'month', 'week', 'today' ];

  // '' is all leagues
  private const LEAGUES = [ '', 'bronze', 'silver', 'gold', 'platinum',
                            'diamond', 'master', 'challenger' ];

  // We only care about ranked Summoner's Rift.
  private const MAP_ID = 1;
  private const QUEUE  = 'ranked';

  // Where to get patch data
  protected const PATCH_URI_PATTERN = "https://ddragon.leagueoflegends.com/realms/%s.json";

  /** @var string[] $regions */
  private $regions = ['na', 'euw', 'www'];

  /**
   * Allows you to select a list of regions to grab data from. By default it
   * will just return data for NA, EUW, and KR.
   *
   * @throws \UnexpectedValueException for any invalid region string in $regions
   * @param GoodBans\ApiClient $client
   * @param RiotAPI\RiotAPI $riot
   * @param string[] ...$regions
   */
  public function __construct(
    ApiClient $client = null, 
    string ...$regions
  ) {    
    parent::__construct($client);

    if (!empty($regions)) {
      if (!isValid(self::REGION_SUBDOMAINS, $regions)) { 
        throw new \UnexpectedValueException(
          "Array of region subdomains contains a value not in OpGG::REGION_SUBDOMAINS."
        ); 
      }

      $this->regions = $regions; 
    }
  }

  /**
   * Validates that all of the values are in $valids
   *
   * @param array $valids
   * @param mixed ...$values
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

  protected function refreshChampions(array $elos = []) : array {
    $data = [];
    foreach ($elos as $elo) {
      $winrates  = $this->getStats('win', $elo);
      $banrates  = $this->getStats('banned', $elo);
      $pickrates = $this->getStats('picked', $elo);
      
      // Check they all have the same number of data points
      $counts = [count($winrates), count($banrates), count($pickrates)];
      if (count(array_unique($counts)) !== 1) {
        throw new \UnexpectedValueException("Counts not equal");
      }

      $champs_by_elo = [];
      foreach ($winrates as $name => $wr) {
        $champs_by_elo[$elo][$name]['winRate'] = $wr;
      }
      foreach ($banrates as $name => $br) {
        $champs_by_elo[$elo][$name]['banRate'] = $br;
      }
      foreach ($pickrates as $name => $pr) {
        $champs_by_elo[$elo][$name]['pickRate'] = $pr;
      }
    }

    $patch = $this->getPatch();
    $champ_objects = [];
    foreach ($champs_by_elo as $elo => $champs) {
      foreach ($champs as $name => $data) {
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

  private function getStats(
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
        http_build_query([
          'type'   => $type,
          'league' => $league,
          'period' => $period,
          'mapId' => self::MAP_ID,
          'queue' => self::QUEUE,
        ])
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