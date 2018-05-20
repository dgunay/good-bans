<?php declare(strict_types=1);

namespace GoodBans;

class BanRanker
{
	protected $db;

	public function __construct(\PDO $db) {
		$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	/**
	 * Determines the N best bans for the current patch using the database.
	 *
	 * @param string $elo Bronze, Silver, Good, or Platinum. Case insensitive.
	 * @param integer $limit How many bans to get.
	 * @return array
	 */
	public function topBans(string $elo = null, $limit = 5) : array {
		$elos = ['BRONZE','SILVER','GOLD','PLATINUM'];

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
				FROM champions
				WHERE elo = '{$elo}'
				ORDER BY banValue DESC
				LIMIT {$limit}
				"
			);

			$top_bans[$elo] = $statement->fetchAll(\PDO::FETCH_ASSOC);
		}

		// Determine the patch we're on
		$statement = $this->db->query(
			"SELECT  patch
			FROM     champions
			GROUP BY patch
			ORDER BY COUNT(*) DESC
			LIMIT    1;
			"
		);

		$patch = $statement->fetchAll(\PDO::FETCH_ASSOC);

		return [
			'patch'    => $patch[0]['patch'],
			'top_bans' => $top_bans
		];
	}
}