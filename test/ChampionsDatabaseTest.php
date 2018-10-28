<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\OpGG;
use GoodBans\Test\Mock\Lolalytics;
use GoodBans\Test\Mock\RiotChampions;
use GoodBans\Logger;
use PHPUnit\Framework\TestCase;
use GoodBans\ChampionsDataSource;

final class ChampionsDatabaseTest extends TestCase
{
	public function testOpGG() {
		$db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			new OpGG(),
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$this->assertTrue(true);
	}

	public function testLolalytics() {
		$db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			new Lolalytics(),
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$this->assertTrue(true);
	}

	/**
	 * Undocumented function
	 *
	 * @dataProvider dataSourceProvider
	 * @param ChampionsDataSource $source
	 * @return void
	 */
	public function testTopBans(ChampionsDataSource $source) {
		$db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			$source,
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$db->refresh();

		$db->topBans();
		$this->assertTrue(true);
	}

	/**
	 * @dataProvider dataSourceProvider
	 * @param ChampionsDataSource $source
	 * @return void
	 */
	public function testGetAll(ChampionsDataSource $source) {
		$db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			$source,
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$db->refresh();

		$champs = $db->getAllChampions();
		$this->assertNotEmpty($champs);
	}

	public function dataSourceProvider() {
		return [
			// Extend these into mocks that return super simple data
			[new OpGG],
			[new Lolalytics()],
		];
	}
}