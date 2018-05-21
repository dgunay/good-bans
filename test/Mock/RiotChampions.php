<?php declare(strict_types=1);

namespace GoodBans\Test\Mock;

use GoodBans\RiotChampions as RealRiotChampions;

/**
 * Uses a locally cached response of Riot's static datadragon API.
 */
class RiotChampions extends RealRiotChampions
{
	// /** @var array $champions */
	// protected $champions;

	// /** @var string $patch */
	// protected $patch;

	// // JSON array of valid DataDragon versions
	const VERSIONS_URI = __DIR__ . '/../data/RiotChampions/versions.json';

	// URI for the fixtures, used in sprintf()
	const FILE_URI_PATTERN = __DIR__ . "/../data/RiotChampions/champion/%s.json";
	
	/**
	 * @throws \DomainException DataDragon 
	 * @param string $patch DataDragon version number.
	 */
	// public function __construct(string $patch) {
	// 	$versions = \json_decode(\file_get_contents(self::VERSIONS_URI), true);

	// 	if ($patch = 'latest') {
	// 		$patch = $versions[0];
	// 	}
	// 	elseif (!in_array($patch, $versions)) {
	// 		throw new \DomainException(
	// 			"$patch is not a valid DataDragon version number (see " . self::VERSIONS_URI . ').'
	// 		);
	// 	}

	// 	$this->patch = $patch;
	// 	$raw_champions = $this->getChampions($patch);
	// 	$this->champions = \json_decode($raw_champions, true)['data'];
	// }

	// protected function getChampions(string $patch) {
	// 	$response = @file_get_contents(
	// 		__DIR__ . "/../data/RiotChampions/champion/{$patch}.json"
	// 	);

	// 	if ($response === false) {
	// 		throw new \RuntimeException(\error_get_last()['message']);
	// 	}

	// 	return $response;
	// }

	/**
	 * Returns a mapping of ['champion key' => 'name']
	 *
	 * @return array
	 */
	// public function getChampNameMap() : array {
	// 	// Map champion ID to name
	// 	$champ_names = [];
	// 	foreach ($this->champions as $champ) {
	// 		$champ_names[$champ['key']] = $champ['name'];
	// 	}
		
	// 	return $champ_names;
	// }

	/**
	 * Returns a mapping of ['champion key' => 'icon URL'].
	 *
	 * @return array
	 */
	// public function getImageUrls() : array {
	// 	$urls = [];
	// 	foreach ($this->champions as $champion) {
	// 		$urls[$champion['key']] = "http://ddragon.leagueoflegends.com/cdn/{$this->patch}/img/champion/{$champion['image']['full']}";
	// 	}

	// 	return $urls;
	// }
}