<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\OpGG;
use GoodBans\Test\Mock\Lolalytics;
use GoodBans\Test\Mock\RiotChampions;
use GoodBans\Logger;
use PHPUnit\Framework\TestCase;

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
}