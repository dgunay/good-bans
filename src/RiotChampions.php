<?php declare(strict_types=1);

namespace GoodBans;

class RiotChampions
{
	protected $champions;
	protected $patch;

	// array of valid DataDragon versions
	const VERSIONS_URL = 'https://ddragon.leagueoflegends.com/api/versions.json';

	public function __construct(string $patch) {
		$versions = \json_decode(\file_get_contents(self::VERSIONS_URL), true);

		if ($patch = 'latest') {
			$patch = $versions[0];
		}
		elseif (!in_array($patch, $versions)) {
			throw new \DomainException("$patch is not a valid DataDragon patch number");
		}

		$this->patch = $patch;
		$raw_champions = \file_get_contents(
			"http://ddragon.leagueoflegends.com/cdn/{$patch}/data/en_US/champion.json"
		);
		$this->champions = \json_decode($raw_champions, true)['data'];
	}

	public function getChampNameMap() : array {
		// Map champion ID to name
		$champ_names = [];
		foreach ($this->champions as $champ) {
			$champ_names[$champ['key']] = $champ['name'];
		}
		
		return $champ_names;
	}

	public function getImageUrls() : array {
		$urls = [];
		foreach ($this->champions as $champion) {
			$urls[$champion['key']] = "http://ddragon.leagueoflegends.com/cdn/{$this->patch}/img/champion/{$champion['image']['full']}";
		}

		return $urls;
	}
}