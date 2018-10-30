<?php declare(strict_types=1);

namespace GoodBans;

/**
 * Handles getting champion data and names from Riot's static DataDragon API.
 * 
 * TODO: use Guzzle or something, this sucks
 */
class RiotChampions
{
	/** @var string[] $versions */
	protected $versions = [];

	/** @var string $cached_version */
	protected $cached_version = null;

	// TODO: add checks for cache state
	/** @var array $cached_champs */
	protected $cached_champs = [];

	/** @var string VERSIONS_URI Points to a JSON array of valid DataDragon versions */
	const VERSIONS_URI = 'https://ddragon.leagueoflegends.com/api/versions.json';

	/** 
	 * @var string FILE_URI_PATTERN Used to sprintf in the version when retrieving 
	 * the file for easier mocking of requests. 
	 */
	const FILE_URI_PATTERN = 'http://ddragon.leagueoflegends.com/cdn/%s/data/en_US/champion.json';

	public function __construct() {
		$versions = \json_decode(\file_get_contents(self::VERSIONS_URI), true);

		// filter out 'lolpatch_*' since they all 403.
		$versions = array_filter($versions, function(string $vsn) : bool {
			return (strpos($vsn, 'lolpatch') === false);
		});

		$this->versions = $versions;	
	}

	/**
	 * @throws \DomainException For invalid DataDragon version number 
	 * @param string $version
	 * @return array
	 */
	public function getChampions(string $version) : array {
		if ($version === 'latest') {
			$version = $this->versions[0];
		}
		elseif (!in_array($version, $this->versions)) {
			throw new \DomainException(
				"$version is not a valid DataDragon version number (see " . self::VERSIONS_URI . ').'
			);
		}

		$response = @file_get_contents(sprintf(self::FILE_URI_PATTERN, $version));

		if ($response === false) {
			throw new \RuntimeException(\error_get_last()['message']);
		}

		$this->cached_champs  = \json_decode($response, true)['data'];
		$this->cached_version = $version;
		return $this->cached_champs;
	}

	/**
	 * Returns a mapping of ['champion key' => 'name']
	 * 
	 * @param string $version A valid DataDragon version.
	 * @return array
	 */
	public function getChampNameMap(string $version = '') : array {
		// refresh if the version isn't the same as the cached version
		if ($version !== $this->cached_version) {
			$this->getChampions($version);
		}
		
		// Map champion ID to name
		$champ_names = [];
		foreach ($this->cached_champs as $champ) {
			$champ_names[$champ['key']] = $champ['name'];
		}
		
		return $champ_names;
	}

	/**
	 * Returns a mapping of ['champion key' => 'icon URL'] using the currently
	 * cached champions.
	 *
	 * @return array
	 */
	public function getImageUrls() : array {
		$urls = [];
		// FIXME: call getChampions() if not cached.
		foreach ($this->cached_champs as $champion) {
			$urls[$champion['key']] = "http://ddragon.leagueoflegends.com/cdn/{$this->cached_version}/img/champion/{$champion['image']['full']}";
		}

		return $urls;
	}
}