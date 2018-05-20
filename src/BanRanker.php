<?php declare(strict_types=1);

namespace GoodBans;

// TODO: use an API to get win% pick% per-elo
class BanRanker
{
	protected $key = '';

	protected $champions = array();

	protected $db;

	public $patch;

	public function __construct(\PDO $db = null) {
		$this->db = $db;
		$this->db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
	}

	public function best_bans(string $elo = null, $limit = 5) {
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