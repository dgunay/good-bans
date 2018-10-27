<?php

namespace GoodBans;

use GoodBans\ApiClient;
use RiotAPI\RiotAPI;

/**
 * Specifies what functionality a data source (i.e. Champion.gg, op.gg, etc)
 * should expose to the user of the class, to allow for aggregation across
 * different data providers.
 */
abstract class ChampionsDataSource
{
  /** @var Champion[][] $champions Map of 'elo' => Champion[] */
  protected $champions = [];

  /** @var ApiClient $client */
  protected $client;

  /** @var RiotAPI\RiotAPI $riot */
  protected $riot;

  public function __construct(ApiClient $client = null, RiotAPI $riot) {
    $this->client = $client ?? new ApiClient();
    $this->riot   = $riot;
  }

  /**
   * Get champions with their data at a given elo, or all of them separated 
   * by elo if none specified.
   * 
   * e.g. [
   *  'bronze' => [...],
   *  'silver' => [...],
   *  ...
   * ]
   * 
   * Where possible, use data that is segregated ONLY by elo, not by role.
   * 
   * @param array $elos
   * @param bool $refresh Force a retrieval of fresh data (don't use cache)
   * @return array should be ['bronze' => [...], 'silver' => [...], ...]
   */
  public function getChampions(array $elos = [], bool $refresh = false) : array {
    // TODO: the filtering might need to be standardized on refreshChampions()
    if ($this->champions && !$refresh) {
      return array_intersect_key($this->champions, array_flip($elos));
    }

    return $this->refreshChampions($elos);
  }

  
  abstract protected function refreshChampions(array $elos = []) : array;

  /**
   * Returns the patch this data source has.
   *
   * @return string
   */
  abstract public function getPatch() : string;
}