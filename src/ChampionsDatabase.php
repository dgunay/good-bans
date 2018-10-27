<?php declare(strict_types=1);

namespace GoodBans;

use GoodBans\ChampionsDataSource;
use GoodBans\RiotChampions;
use GoodBans\Champion;
use GoodBans\Logger;
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

	public function __construct(
		\PDO $pdo, 
		ChampionsDataSource $champion_gg,
		RiotChampions $riot_champions,
		Logger $logger = null
	) {
		$this->db = $pdo;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$this->champion_gg = $champion_gg;
		$this->riot_champions = $riot_champions;

		if ($logger === null) {
			// log to phpout by default
			$this->logger = new Logger(fopen('php://output', 'w')); 
		}
		else {
			$this->logger = $logger;
		}
	}

	public function refresh() {
		$elos = [
			'bronze'     => [],
			'silver'     => [],
			'gold'       => [],
			'platinum'   => [],
			// 'diamond'    => [],
			// 'master'     => [],
			// 'challenger' => [],
		];

		// $patch = null;

		// get each elo's champ stats
		foreach ($elos as $elo => $champs) {
			$this->logger->log(LogLevel::INFO, "getting $elo champ stats..." . PHP_EOL);
			$champions = $this->champion_gg->getChampions($elo);
			$elos[$elo] = $champions = $this->champion_gg->aggregateRoles();
		}
		
		// Map champion ID to name
		$champ_names = $this->riot_champions->getChampNameMap('latest');

		$this->logger->log(LogLevel::INFO, 'Creating table if it does not exist...' . PHP_EOL);
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS champions (
				id TEXT, winRate REAL, playRate REAL, `name` TEXT, elo TEXT, 
				banValue REAL, banRate REAL, adjustedPickRate REAL, `patch` TEXT, 
				img TEXT
			)"
		);

		// flush champs in the database
		$this->logger->log(LogLevel::INFO, 'Clearing database...' . PHP_EOL);
		$this->db->query("DELETE FROM champions");

		$img_urls = $this->riot_champions->getImageUrls();
		// spin up our DB and insert our champions, one row per elo
		$this->logger->log(LogLevel::INFO, 'Populating database...' . PHP_EOL);
		foreach ($elos as $elo => $champions) {

			// TODO: needs refactor after ChampionsDataSource
			foreach ($champions as $champ_gg_raw_data) {
				$champion = new Champion(
					$champ_gg_raw_data, 
					$champ_names[$champ_gg_raw_data['championId']]
				);

				// Bind our values for protection against SQL injection
				$statement = $this->db->prepare("INSERT INTO champions (
					id, winRate, playRate, name, elo, banValue, banRate, adjustedPickRate, patch, img
				)
				VALUES (
					:id, :winRate, :playRate, :name, :elo, :banValue, :banRate,:adjustedPickRate, :patch, :img
				)");

				$statement->execute([
					':id'               => $champion->getId(),
					':winRate'          => $champion->getWinRate(),
					':playRate'         => $champion->getPlayRate(),
					':name'             => $champion->getName(),
					':elo'              => $champion->getElo(),
					':adjustedPickRate' => $champion->adjustedPickRate(),
					':banRate'          => $champion->getBanRate(),
					':banValue'         => $champion->banValue(),
					':patch'            => $champion->getPatch(),
					':img'              => $img_urls[$champion->getId()],
				]);
			}
		}
	}

	protected function getPatch(array $champions) : string {
		$patches = [];
		foreach ($champions as $champion) {
			$patches[] = $champion['patch'];
		}

		// most common patches
		$values = array_count_values($patches);
		arsort($values);
		return array_keys($values)[0];
	}

	/**
	 * Aggregates champion data for all roles. For example, a champion played in
	 * mid and top will have their mid and top winrate and banrate averaged, and
	 * their play rates summed.
	 *
	 * @param array $champion_gg_data
	 * @return array
	 */
	private function aggregateChamps(array $champion_gg_data) : array {
		// TODO: need to weight the average of each role by roleplaypercentage
		// TOdO: how can I use the Champion() class here?
		$champions = [];
		// TODO: debug this until it's ironclad
		foreach ($champion_gg_data as $champion) {
			if (is_array($champion['winRate'])) {
				// aggregate champion data as arrays
				// $champion['winRate'][]  = $champion['winRate'] * $champion['percentRolePlayed'];
				$champion['winRate'][]  = $champion['winRate'];
				$champion['banRate'][]  = $champion['banRate'];				
				$champion['playRate']  += $champion['playRate'];
			}
			else {
				// if this champ is new, reinitialize it as an array
				// $champion['winRate']  = [$champion['winRate'] * $champion['percentRolePlayed']];
				$champion['winRate']  = [$champion['winRate']];
				$champion['banRate']  = [$champion['banRate']];
				$champion['playRate'] = $champion['playRate'];
			}

			$champions[$champion['championId']] = $champion;
		}

		foreach ($champions as $id => $champion) {
			// average wr and banrate
			$champion['winRate'] = array_sum($champion['winRate']) / count($champion['winRate']);
			$champion['banRate'] = array_sum($champion['banRate']) / count($champion['banRate']);

			$champions[$id] = $champion;
		}

		return $champions;
	}
}