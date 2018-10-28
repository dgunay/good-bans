<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\View;
use GoodBans\Logger;
use GoodBans\ChampionsDataSource;
use GoodBans\ChampionsDatabase;
use GoodBans\Test\Mock\OpGG;
use GoodBans\Test\Mock\RiotChampions;
use GoodBans\Test\Mock\Lolalytics;

use PHPUnit\Framework\TestCase;

final class ViewTest extends TestCase
{
/**
	 * @dataProvider dataSourceProvider
	 * @param ChampionsDataSource $source
	 * @return void
	 */
	public function testEndToEnd(ChampionsDataSource $source) {
		$db = new ChampionsDatabase(
			new \PDO('sqlite::memory:'),
			$source,
			new RiotChampions(),
			new Logger(fopen('php://memory', 'w'))
		);

		$db->refresh();
		
		$view = new View($db->topBans());
		$this->assertTrue(true);
	}

	public function dataSourceProvider() {
		return [
			// Extend these into mocks that return super simple data
			[new OpGG],
			[new Lolalytics()],
		];
	}}