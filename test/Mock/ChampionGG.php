<?php declare(strict_types=1);

namespace GoodBans\Test\Mock;

use GoodBans\ChampionGG as RealChampionGG;

/**
 * A mock version of the ChampionGG class that just uses local files to
 * simulate responses from the champion.gg API.
 * 
 * TODO: test for:
 *  - no malformed args are being sent
 *  - unexpected responses are dealt with gracefully
 */
class ChampionGG extends RealChampionGG
{
	protected $key;
	protected $champions = false;
	protected $patch = null;

	public function __construct(string $api_key = null) {
		$this->key = $api_key;
	}

	/**
	 * Gets all champion data from the champion.gg API.
	 * 
	 * Data is aggregated for all elos if no argument is given.
	 *
	 * @param string $elo bronze, silver, gold, or platinum.
	 * @return array
	 */
	public function getChampions(string $elo = null) : array {
		$params = [
			'limit'     => 1000, 
			'champData' => 'elo,playRate,winRate', // all we care about
			'api_key'   => $this->key
		];

		if ($elo) {
			$params['elo'] = strtoupper($elo);
		}
		
		$response = $this->get(__DIR__ . "/../data/ChampionGG/testGetChampions/{$elo}.json");

		$this->champions = json_decode($response, true);
		
		return $this->champions;
	}

	/**
	 * Fakes a request to the champion.gg API by loading a local file.
	 *
	 * @param string $uri
	 * @param array $args
	 * @return string
	 */
	protected function get(string $uri, array $args = []) : string {
		$response = @file_get_contents($uri);

		if ($response === false) {
			throw new \RuntimeException(\error_get_last()['message']);
		}

		return $response;
	}

	/**
	 * Returns the most common patch number found among the champion data. 
	 *
	 * @return string
	 */
	public function getPatch() : string {
		if ($this->patch) {
			return $this->patch;
		}

		if ($this->champions === false) {
			throw new \Exception('Champions not cached yet (call getChampions() first.');
		}

		$patches = [];
		foreach ($this->champions as $champion) {
			$patches []= $champion['patch'];
		}

		// return most commonly occuring patch number
		$counted = array_count_values($patches);
		arsort($counted);
		$this->patch = (key($counted));
		return $this->patch;
	}

	public function json() : string {
		if ($this->champions === false) {
			throw new \Exception('Champions not cached yet (call getChampions() first.');
		}

		return json_encode($this->champions);
	}
}