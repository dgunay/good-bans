<?php

namespace GoodBans;

/**
 * Specifies what functionality a data source (i.e. Champion.gg, op.gg, etc)
 * should expose to the user of the class, to allow for aggregation.
 */
interface ChampionsData
{
  /**
   * Get champions with their data at a given elo, or all of them separated 
   * by elo if none specified
   *
   * @param array $elos
   * @return array should be ['bronze' => [...], 'silver' => [...], ...]
   */
  public function getChampions(array $elos = []) : array;

  // dump the internal data model as JSON
  public function json() : string;
}