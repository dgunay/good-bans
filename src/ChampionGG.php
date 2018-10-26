<?php declare(strict_types=1);

namespace GoodBans;

class ChampionGG extends \GoodBans\ChampionsDataSource
{
	protected $champions = false;
	protected $patch     = null;
	protected $key       = null;

	const ELOS = ['bronze', 'silver', 'gold', 'platinum'];

	/**
	 * Gets all champion data from the champion.gg API.
	 * 
	 * Data is aggregated for all elos if no argument is given.
	 *
	 * @param string $elo bronze, silver, gold, or platinum.
	 * @return array
	 */
	public function refreshChampions(array $elos = []) : array {
		$params = [
			'limit'     => 1000, 
			'champData' => 'elo,playRate,winRate', // all we care about
			'api_key'   => $this->client->getCredentials(),
		];

		// TODO: condense these with an array intersect operation maybe?
		if (empty($elos)) {
			// TODO: how to reconcile this with fixture data?
			foreach (self::ELOS as $elo) {
				$params['elo'] = strtoupper($elo);
				$response = $this->get($params);
				$this->champions[$elo] = json_decode($response, true);
			}
		}
		else {
			foreach ($elos as $elo) {
				$params['elo'] = strtoupper($elo);
				$response = $this->get($params);
				$this->champions[$elo] = json_decode($response, true);
			}
		}

		return $this->aggregateRoles();	
	}

	public function aggregateRoles() : array {
		$aggregate_champions = [];
		// TODO: debug this until it's ironclad
		foreach ($this->champions as $elo => $champions) {
			foreach ($champions as $champion) {
				$id = $champion['championId'];
				if (array_key_exists($id, $aggregate_champions)) {
					// aggregate champion data as arrays
					// TODO: the math needs a look here
					// TODO: how do you do weighted averages of winrate without bringing down the numbers super far?
					$aggregate_champions[$elo][$id]['winRate'][]  = ($champion['winRate'] * $champion['percentRolePlayed']);
					$aggregate_champions[$elo][$id]['banRate'][]  = $champion['banRate'];				
					$aggregate_champions[$elo][$id]['playRate']  += $champion['playRate'];
				}
				else {
					// if this champ is new, reinitialize averaged fields as array
					$aggregate_champions[$elo][$id] = $champion;
					$aggregate_champions[$elo][$id]['winRate']  = [$champion['winRate'] * $champion['percentRolePlayed']];
					$aggregate_champions[$elo][$id]['banRate']  = [$champion['banRate']];
					$aggregate_champions[$elo][$id]['playRate'] = $champion['playRate'];
				}
			}
		}

		// print_r($aggregate_champions['bronze']['266']); exit;

		foreach ($aggregate_champions as $elo => $champions) {
			foreach ($champions as $id => $champion) {
				// average wr and banrate
				$champion['winRate'] = array_sum($champion['winRate']) / count($champion['winRate']);
				$champion['banRate'] = array_sum($champion['banRate']) / count($champion['banRate']);

				$aggregate_champions[$elo][$id] = new Champion($champion);
			}
		}

		return $aggregate_champions;
	}

	/**
	 * Makes a GET request to the champions endpoint.
	 * 
	 * @param array $args
	 * @return string
	 */
	protected function get(array $args = []) : string {
		$args['api_key'] = $this->client->getCredentials();
		$response = $this->client->get('http://api.champion.gg/v2/champions', $args);
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
			$this->getChampions();
		}

		$patches = [];
		foreach ($this->champions as $champion) {
			$patches[] = $champion['patch'];
		}

		// return most commonly occuring patch number
		$counted = array_count_values($patches);
		arsort($counted);
		$this->patch = (key($counted));
		return $this->patch;
	}

	public function json() : string {
		if ($this->champions === false) {
			$this->getChampions();
		}

		return json_encode($this->champions);
	}
}