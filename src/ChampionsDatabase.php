<?php declare(strict_types=1);

namespace GoodBans;

use GoodBans\ChampionsDataSource;
use GoodBans\RiotChampions;
use GoodBans\Champion;
use GoodBans\Logger;
use GoodBans\TopBans;
use Psr\Log\LogLevel;


class ChampionsDatabase
{
	/** @var \PDO */
	protected $db;

	/** @var ChampionsDataSource */
	protected $champion_data;

	/** @var RiotChampions */
	protected $riot_champions;

	/** @var Logger */
	protected $logger;

	/** @var string $patch */
	protected $patch;

	public function __construct(
		\PDO $pdo, 
		ChampionsDataSource $champion_data,
		RiotChampions $riot_champions,
		Logger $logger = null
	) {
		$this->db = $pdo;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		$this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);

		$this->champion_data  = $champion_data;
		$this->riot_champions = $riot_champions;

		if ($logger === null) {
			// log to phpout by default
			$this->logger = new Logger(fopen('php://output', 'w')); 
		}
		else {
			$this->logger = $logger;
		}
	}

	public function initializeTables() {
		$this->logger->log(LogLevel::INFO, 'Creating tables if they do not exist...' . PHP_EOL);
		foreach ($this->champion_data->getElos() as $elo) {
			$this->db->query(
				"CREATE TABLE IF NOT EXISTS `champions_{$elo}` (
					id TEXT, winRate REAL, playRate REAL, `name` TEXT, banValue REAL, 
					banRate REAL, adjustedPickRate REAL, `patch` TEXT, img TEXT
				)"
			);	
		}
	}

	public function refresh() {
		$elos = $this->champion_data->getElos();
		
		// get each elo's champ stats
		$this->logger->log(
			LogLevel::INFO, 
			'getting stats for '.implode(', ', $elos). '...' . PHP_EOL
		);
		$champs_by_elo = $this->champion_data->getChampions($elos);
		
		// Map champion ID to name
		$champ_names = $this->riot_champions->getChampNameMap('latest');
		$name_map = array_flip($champ_names);

		// Make the table if it doesn't exist
		$this->initializeTables();

		// flush champs in the database
		$this->logger->log(LogLevel::INFO, 'Clearing database...' . PHP_EOL);
		foreach ($this->champion_data->getElos() as $elo) {
			$this->db->query("DELETE FROM `champions_{$elo}`");
		}

		$img_urls = $this->riot_champions->getImageUrls();
		// spin up our DB and insert our champions, one row per elo
		$this->logger->log(LogLevel::INFO, 'Populating database...' . PHP_EOL);
		foreach ($champs_by_elo as $elo => $champions) {
			foreach ($champions as $champion) {
				// Bind our values for protection against SQL injection
				$statement = $this->db->prepare("INSERT INTO champions_{$elo} (
					id, winRate, playRate, name, banValue, banRate, adjustedPickRate, patch, img
				)
				VALUES (
					:id, :winRate, :playRate, :name, :banValue, :banRate,:adjustedPickRate, :patch, :img
				)");
				
				$statement->execute([
					':id'               => $champion->getChampionId() ?? $name_map[$champion->getName()],
					':winRate'          => $champion->getWinRate(),
					':playRate'         => $champion->getPlayRate(),
					':name'             => $champion->getName(),
					':adjustedPickRate' => $champion->adjustedPickRate(),
					':banRate'          => $champion->getBanRate(),
					':banValue'         => $champion->banValue(),
					':patch'            => $champion->getPatch(),
					':img'              => $img_urls[$name_map[$champion->getName()]],
				]);
			}
		}
	}

	public function getAllChampions() {
		$champs = [];
		foreach ($this->champion_data->getElos() as $elo) {
			$champs[$elo] = $this->db->query("SELECT * from `champions_{$elo}`")->fetchAll();
		}
		return $champs;
	}

	/**
	 * Determines the N best bans for the current patch using the database.
	 *
	 * @param string $elo Bronze, Silver, Good, or Platinum. Case insensitive.
	 * @param integer $limit How many bans to get.
	 * @return array
	 */
	public function topBans(string $elo = null, $limit = 5) : TopBans {
		$elos = $this->champion_data->getElos();

		// optionally filter by one elo
		if ($elo) {
			$elos = array_filter($elos, function ($a) use ($elo) {
				return strcasecmp($a, $elo) === 0;
			});
		}
		
		$top_bans = [];
		foreach ($elos as $elo) {
			// select the top N 
			$statement = $this->db->query(
				"SELECT * 
				FROM `champions_{$elo}`
				ORDER BY banValue DESC
				LIMIT {$limit}
				"
			);

			$top_bans[$elo] = $statement->fetchAll(\PDO::FETCH_ASSOC);
		}

		return new TopBans($top_bans, $this->getPatch());
	}

	public function getPatch() : string {
		if ($this->patch) { return $this->patch; }

		// FIXME: assumes that champions_silver is always populated and the first
		// result is the correct patch.
		return $this->db->query(
			"SELECT   `patch`
			 FROM     `champions_silver`;
			 GROUP BY `patch`
			 ORDER BY COUNT(*) DESC
			 LIMIT    1"
		)->fetchAll()[0]['patch'];
	}
}