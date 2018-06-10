<?php

namespace GoodBans;

use GoodBans\ApiClient;

/**
 * Specifies what functionality a data source (i.e. Champion.gg, op.gg, etc)
 * should expose to the user of the class, to allow for aggregation across
 * different data providers.
 */
abstract class ChampionsDataSource
{
  /** @var array */
  protected $champions = [];

  /** @var ApiClient */
  protected $client;

  public function __construct(ApiClient $client = null) {
    $this->client = $client ?? new ApiClient();
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

  // TODO: define an iterable/keyval store of type Champion
  abstract protected function refreshChampions(array $elos = []) : array;
}