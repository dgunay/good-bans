<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\ChampionGG;
use GoodBans\Test\Mock\RiotChampions;
use PHPUnit\Framework\TestCase;

final class ChampionsDatabaseTest // extends TestCase
{
	protected $db;

	protected function setUp() {
		$this->db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			new ChampionGG(),
			new RiotChampions()
		);
	}

	protected function tearDown() {
		$this->db = null;
	}

	
}