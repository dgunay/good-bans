<?php declare(strict_types=1);

namespace GoodBans;

class ChampionGG
{
	protected $key;
	protected $champions = false;
	protected $patch = null;

	public function __construct(string $api_key) {
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
		
		$response = $this->get("champions", $params);

		$this->champions = json_decode($response, true);

		$this->patch = $this->getPatch();
		
		return $this->champions;
	}

	/**
	 * Makes a GET request to the champion.gg API.
	 *
	 * @param string $endpoint
	 * @param array $args
	 * @return string
	 */
	private function get(string $endpoint, array $args = []) : string {
		$args['api_key'] = $this->key;
		return file_get_contents(
			'http://api.champion.gg/v2/' . $endpoint . '?' . http_build_query($args)
		);
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