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

	public function get_champions(string $elo = null) : array {
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

		$this->patch = $this->get_patch();
		
		return $this->champions;
	}

	private function get(string $endpoint, array $args = []) : string {
		$args['api_key'] = $this->key;
		return file_get_contents(
			'http://api.champion.gg/v2/' . $endpoint . '?' . http_build_query($args)
		);
	}

	public function get_patch() : string {
		if ($this->patch) {
			return $this->patch;
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
			throw new \Exception('Champions not cached yet.');
		}

		return json_encode($this->champions);
	}
}