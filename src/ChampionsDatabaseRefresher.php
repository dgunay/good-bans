<?php declare(strict_types=1);

namespace GoodBans;

use GoodBans\ChampionsGG;
use GoodBans\RiotChampions;
use GoodBans\Champion;

class ChampionsDatabaseRefresher
{
	protected $db;
	protected $champion_gg;
	protected $riot_champions;

	public function __construct(
		\PDO $pdo, 
		ChampionGG $champion_gg,
		RiotChampions $riot_champions
	) {
		$this->db = $pdo;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

		$this->champion_gg = $champion_gg;
		$this->riot_champions = $riot_champions;
	}

	public function refresh() {
		$elos = [
			'bronze'     => [],
			'silver'     => [],
			'gold'       => [],
			'platinum'   => [],
			'diamond'    => [],
			'master'     => [],
			'challenger' => [],
		];

		// get each elo's champ stats
		foreach ($elos as $elo => $champs) {
			echo "getting $elo champ stats..." . PHP_EOL;
			$champions = $this->champion_gg->getChampions($elo);
			$elos[$elo] = $this->aggregate_champs($champions);
		}
		
		// Map champion ID to name
		$champ_names = $this->riot_champions->getChampNameMap();

		echo 'Creating table if it does not exist...' . PHP_EOL;
		$this->db->query(
			"CREATE TABLE IF NOT EXISTS champions (
				id TEXT, winRate REAL, playRate REAL, `name` TEXT, elo TEXT, 
				banValue REAL, adjustedPickRate REAL, `patch` TEXT, img TEXT
			)"
		);

		// flush champs in the database
		echo 'Clearing database...' . PHP_EOL;
		$this->db->query("DELETE FROM champions");

		$img_urls = $this->riot_champions->getImageUrls();
		// spin up our DB and insert our champions, one row per elo
		echo 'Populating database...' . PHP_EOL;
		foreach ($elos as $elo => $champions) {
			foreach ($champions as $champ_gg_raw_data) {
				$champion = new Champion(
					$champ_gg_raw_data, 
					$champ_names[$champ_gg_raw_data['championId']]
				);

				// Bind our values for protection against SQL injection
				$statement = $this->db->prepare("INSERT INTO champions (
					id, winRate, playRate, name, elo, banValue, adjustedPickRate, patch, img
				)
				VALUES (
					:id, :winRate, :playRate, :name, :elo, :banValue, :adjustedPickRate, :patch, :img
				)");

				$statement->execute([
					':id'               => $champion->getId(),
					':winRate'          => $champion->getWinRate(),
					':playRate'         => $champion->getPlayRate(),
					':name'             => $champion->getName(),
					':elo'              => $champion->getElo(),
					':adjustedPickRate' => $champion->adjustedPickRate(),
					':banValue'         => $champion->banValue(),
					':patch'            => $champion->getPatch(),
					':img'              => $img_urls[$champion->getId()],
				]);
			}
		}
	}

	/**
	 * Aggregates champion data for all roles. For example, a champion played in
	 * mid and top will have their mid and top winrate and banrate averaged, and
	 * their play rates summed.
	 *
	 * @param array $champion_gg_data
	 * @return array
	 */
	private function aggregate_champs(array $champion_gg_data) : array {
		$champions = [];
		foreach ($champion_gg_data as $champion) {
			if (is_array($champion['winRate'])) {
				// aggregate champion data as arrays
				$champion['winRate'][]  = [$champion['winRate']];
				$champion['banRate'][]  = [$champion['banRate']];				
				$champion['playRate']   += $champion['playRate'];
			}
			else {
				// if this champ is new, reinitialize it as an array
				$champion['winRate']  = array($champion['winRate']);
				$champion['banRate']  = array($champion['banRate']);
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

	public function pdo() : \PDO {
		return $this->db;
	}
}