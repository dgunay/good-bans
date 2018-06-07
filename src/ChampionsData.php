<?php

namespace GoodBans;

/**
 * Specifies what functionality a data source (i.e. Champion.gg, op.gg, etc)
 * should expose to the user of the class, to allow for aggregation.
 */
abstract class ChampionsData
{
  /** @var array */
  protected $champions = null;

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
   * @return array should be ['bronze' => [...], 'silver' => [...], ...]
   */
  public function getChampions(array $elos = []) : array {
    if ($this->champions) {
      return array_intersect_key($this->champions, array_flip($elos));
    }

    return $this->refreshChampions($elos);
  }

  // TODO: define an iterable/keyval store of type Champion
  abstract protected function refreshChampions(array $elos = []) : array;
}