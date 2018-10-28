<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\OpGG;
use GoodBans\Test\Mock\RiotChampions;
use GoodBans\Logger;
use PHPUnit\Framework\TestCase;

final class ChampionsDatabaseTest extends TestCase
{
	protected $db;

	protected function setUp() {
		$this->db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			new OpGG(),
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$this->db->refresh();
	}

	public function testThing() {
		$this->assertTrue(true);
	}
	
	protected function tearDown() {
		$this->db = null;
	}

}