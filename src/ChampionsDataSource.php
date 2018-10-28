<?php

namespace GoodBans;

use GoodBans\ApiClient;
use RiotAPI\RiotAPI;
use RiotAPI\Definitions\Region;

/**
 * Specifies what functionality a data source (i.e. Champion.gg, op.gg, etc)
 * should expose to the user of the class, to allow for aggregation across
 * different data providers.
 */
abstract class ChampionsDataSource
{
  /** @var Champion[][] $champions Map of 'elo' => Champion[] */
  protected $champions = [];

  /** @var string $patch */
  protected $patch;
  
  /** @var ApiClient $client */
  protected $client;

  /**
   * TODO: document
   *
   * @param ApiClient $client
   * @param RiotAPI $riot
   */
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

    $champs = $this->refreshChampions($elos);
    foreach ($champs as $elo => $c) {
      if (!$this->containsOnlyChampions($c)) {
        throw new \UnexpectedValueException(
          "Array does not contain only Champion objects"
        );
      }
    }

    return $champs;
  }

  protected function containsOnlyChampions(array $champs) : bool {
    foreach ($champs as $champ) {
      if (!($champ instanceof Champion)) { return false; }
    }

    return true;
  }

  abstract protected function refreshChampions(array $elos = []) : array;

  /**
   * Returns the patch this data source has.
   *
   * @return string
   */
  abstract public function getPatch() : string;

  /**
   * Returns all elos that the data source aggregates as lowercased strings.
   *
   * @return array
   */
  abstract public function getElos() : array;
}