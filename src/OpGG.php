<?php

namespace GoodBans;

use GoodBans\ChampionsDataSource;
use GoodBans\ApiClient;

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

  /** @var string[] $regions */
  private $regions = ['na', 'euw', 'www'];

  /**
   * Allows you to select a list of regions to grab data from. By default it
   * will just return data for NA, EUW, and KR.
   *
   * @throws \UnexpectedValueException for any invalid region string.
   * @param ApiClient $client
   * @param string[] ...$regions
   */
  public function __construct(ApiClient $client = null, string ...$regions) {    
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

  // TODO: define an iterable/keyval store of type Champion
  protected function refreshChampions(array $elos = []) : array {
    return [];

    foreach ($elos as $elo) {
      // TODO: get the stuff 
    }
  }

  private function getStats(
    string $type = 'win', 
    string $league = '', 
    string $period = 'month'
  ) {
    if (!$this->isValid(self::GRAPH_TYPES, $type)) {
      throw new \UnexpectedValueException("Type '$type' is invalid.");
    }
    if (!$this->isValid(self::LEAGUES, $league)) {
      throw new \UnexpectedValueException("League '$league' is invalid.");
    }
    if (!$this->isValid(self::PERIODS, $period)) {
      throw new \UnexpectedValueException("Period '$period' is invalid.");
    }

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
    }

    // parse the response
    $stats = $this->parseAjaxResponse($response);

    return $stats;
  }

  private function parseAjaxResponse(string $html) : array {
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $x = new \DOMXPath($dom);

    // TODO: xpaths
    $rows = $x->query("//tbody[@class='content']/tr");
    if ($rows->count()) {
      foreach ($rows as $row) {
        
      }
    }
    else {
      throw new \Exception('No rows found.');
    }
  }

  // private function getFromXpath(\DOMXPath $x, string $xpath, \DOMNode $ctx = null) {
  //   $nodes = $x->query($xpath, $ctx);
  //   $result = null;
  //   if ($nodes->count()) { $result = }
  // }

  /**
   * Returns the patch this data source has.
   *
   * @return string
   */
  public function getPatch() : string {
    // TODO:
    return 'not implemented.';
  }
}