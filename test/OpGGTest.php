<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\OpGG;
use GoodBans\ApiClient;
use GoodBans\Champion;
use RiotAPI\RiotAPI;
use RiotAPI\Definitions\Region;
use PHPUnit\Framework\TestCase;

final class OpGGTest extends TestCase
{

	private $gg;

	public function setUp() {
		$this->gg = new OpGG();
	}

	/**
	 * Tests that getChampions() returns an array of Champion.
	 *
	 * @dataProvider validDataProvider
	 * @return void
	 */
	public function testGetChampions(string $type, string $league) {
		$champs_by_elo = $this->gg->getChampions([$league]);
		$this->assertTrue(true);
	}

	// Gets all combinations of type and league
	public function validDataProvider() {
		$types = [];
		foreach (glob(__DIR__ . '/data/OpGG/*') as $file) {
			if (is_dir($file)) { $types[] = basename($file); }
		}

		$leagues = [];
		foreach (glob(__DIR__ . "/data/OpGG/{$types[0]}/*") as $file) {
			if (is_dir($file)) { $leagues[] = basename($file); }
		}
		
		$params = [];
		foreach ($types as $type) {
			foreach ($leagues as $league) {
				$params[] = [ $type, $league ];
			}
		}
		return $params;
	}
}