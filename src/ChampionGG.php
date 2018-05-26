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
		
		return $this->champions;
	}

	public function aggregateRoles() {
		// TODO: need to weight the average of each role by roleplaypercentage
		$aggregate_champions = [];
		// TODO: debug this until it's ironclad
		foreach ($this->champions as $champion) {
			$id = $champion['championId'];
			if (array_key_exists($id, $aggregate_champions)) {
				// aggregate champion data as arrays
				$aggregate_champions[$id]['winRate'][]  = $champion['winRate'] * $champion['percentRolePlayed'];
				// $aggregate_champions[$id]['winRate'][]  = $champion['winRate'];
				$aggregate_champions[$id]['banRate'][]  = $champion['banRate'];				
				$aggregate_champions[$id]['playRate']  += $champion['playRate'];
			}
			else {
				// if this champ is new, reinitialize averaged fields as array
				$aggregate_champions[$id] = $champion;
				$aggregate_champions[$id]['winRate']  = [$champion['winRate'] * $champion['percentRolePlayed']];
				// $champion[$id]['winRate']  = [$champion['winRate']];
				$aggregate_champions[$id]['banRate']  = [$champion['banRate']];
				$aggregate_champions[$id]['playRate'] = $champion['playRate'];
			}
		}

		foreach ($aggregate_champions as $id => $champion) {
			// average wr and banrate
			$champion['winRate'] = array_sum($champion['winRate']) / count($champion['winRate']);
			$champion['banRate'] = array_sum($champion['banRate']) / count($champion['banRate']);

			$champions[$id] = $champion;
		}

		return $champions;
	}

	/**
	 * Makes a GET request to the champion.gg API.
	 *
	 *  TODO: use Guzzle, this sucks
	 * 
	 * @param string $endpoint
	 * @param array $args
	 * @return string
	 */
	protected function get(string $endpoint, array $args = []) : string {
		$args['api_key'] = $this->key;
		$response = @file_get_contents(
			'http://api.champion.gg/v2/' . $endpoint . '?' . http_build_query($args)
		);

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