<?php declare(strict_types=1);

namespace GoodBans;

/**
 * Handles getting champion data and names from Riot's static DataDragon API.
 * 
 * TODO: use Guzzle or something, this sucks
 */
class RiotChampions
{
	/** @var array $champions */
	protected $champions;

	/** @var string $patch */
	protected $patch;

	// JSON array of valid DataDragon versions
	const VERSIONS_URI = 'https://ddragon.leagueoflegends.com/api/versions.json';

	/**
	 * @throws \DomainException DataDragon 
	 * @param string $patch DataDragon version number.
	 */
	public function __construct(string $patch) {
		$versions = \json_decode(\file_get_contents(self::VERSIONS_URI), true);

		// filter out 'lolpatch_*' since they all 403.
		$versions = array_filter($versions, function($vsn) {
			return strpos($vsn, 'lolpatch') === false;
		});
		
		if ($patch = 'latest') {
			$patch = $versions[0];
		}
		elseif (!in_array($patch, $versions)) {
			// TODO: why doesn't 'version_that_doesnt_exist' make this throw?
			throw new \DomainException(
				"$patch is not a valid DataDragon version number (see " . self::VERSIONS_URI . ').'
			);
		}

		$this->patch = $patch;
		$raw_champions = $this->getChampions($patch);
		$this->champions = \json_decode($raw_champions, true)['data'];
	}

	protected function getChampions(string $patch) {
		$response = @file_get_contents(
			"http://ddragon.leagueoflegends.com/cdn/{$patch}/data/en_US/champion.json"
		);

		if ($response === false) {
			throw new \RuntimeException(\error_get_last()['message']);
		}

		return $response;
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