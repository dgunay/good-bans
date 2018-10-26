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
  private const REGION_SUBDOMAINS = [
    'na',
    'www', // korea
    'jp',
    'euw',
    'eune',
    'oce',
    'br',
    'las',
    'lan',
    'ru',
    'tr',
    'sg',
    'id',
    'ph',
    'tw',
    'vn',
    'th',
  ];

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
      if (!regionsAreValid($regions)) { 
        throw new \UnexpectedValueException(
          "Array of region subdomains contains a value not in OpGG::REGION_SUBDOMAINS."
        ); 
      }

      $this->regions = $regions; 
    }
  }

  private function regionsAreValid(string ...$regions) : bool {
    foreach ($regions as $region) {
      if (!in_array($region, self::REGION_SUBDOMAINS)) {
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

  private function get(
    // TODO: enumerate valid inputs for each of these
    string $type = 'win', 
    string $league = '', // blank defaults to showing All leagues
    string $period = 'month', 
    int $mapId = 1, // 1 = SR
    string $queue = 'ranked'
  ) {
    throw new \Exception('not implemeted');
  }

  /**
   * Returns the patch this data source has.
   *
   * @return string
   */
  public function getPatch() : string {
    return 'not implemented.';
  }
}