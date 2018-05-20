<?php declare(strict_types=1);

namespace GoodBans\Test\Mock;

/**
 * Uses a locally cached response of Riot's static datadragon API.
 */
class RiotChampions extends GoodBans\RiotChampions
{
	/** @var array $champions */
	protected $champions;

	/** @var string $patch */
	protected $patch;

	// JSON array of valid DataDragon versions
	const VERSIONS_URL = 'https://ddragon.leagueoflegends.com/api/versions.json';

	/**
	 * @throws \DomainException DataDragon 
	 * @param string $patch DataDragon version number.
	 */
	public function __construct(string $patch) {
		$versions = \json_decode(\file_get_contents(self::VERSIONS_URL), true);

		if ($patch = 'latest') {
			$patch = $versions[0];
		}
		elseif (!in_array($patch, $versions)) {
			throw new \DomainException(
				"$patch is not a valid DataDragon version number (see " . self::VERSIONS_URL . ').'
			);
		}

		$this->patch = $patch;
		$raw_champions = \file_get_contents(
			"http://ddragon.leagueoflegends.com/cdn/{$patch}/data/en_US/champion.json"
		);
		$this->champions = \json_decode($raw_champions, true)['data'];
	}

	/**
	 * Returns a mapping of ['champion key' => 'name']
	 *
	 * @return array
	 */
	public function getChampNameMap() : array {
		// Map champion ID to name
		$champ_names = [];
		foreach ($this->champions as $champ) {
			$champ_names[$champ['key']] = $champ['name'];
		}
		
		return $champ_names;
	}

	/**
	 * Returns a mapping of ['champion key' => 'icon URL'].
	 *
	 * @return array
	 */
	public function getImageUrls() : array {
		$urls = [];
		foreach ($this->champions as $champion) {
			$urls[$champion['key']] = "http://ddragon.leagueoflegends.com/cdn/{$this->patch}/img/champion/{$champion['image']['full']}";
		}

		return $urls;
	}
}