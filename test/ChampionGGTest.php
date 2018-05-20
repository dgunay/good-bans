<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\ChampionGG;
use PHPUnit\Framework\TestCase;

final class ChampionGGTest extends TestCase
{
	protected $gg;

	const ELOS = ['bronze', 'silver', 'gold', 'platinum'];

	public function setUp() {
		$this->gg = new ChampionGG();
	}

	/**
	 * Tests that getChampions() makes a request and decodes the json.
	 *
	 * @return void
	 */
	public function testGetChampions() {
		foreach (self::ELOS as $case) {
			$result = $this->gg->getChampions($case);

			$this->assertSame(
				$result,
				json_decode(
					file_get_contents(
						__DIR__ . "/data/ChampionGG/testGetChampions/{$case}.json"
					),
					true
				)
			);
		}

		$exceptional_cases = [
			'uri_that_doesnt_exist'
		];
		
		foreach ($exceptional_cases as $case) {
			$this->expectException(\RuntimeException::class);

			$this->gg->getChampions($case);
		}
	}

	/**
	 * Test that the getPatch() function correctly determines the patch from the
	 * downloaded data of each elo, which was cached for patch 8.10.
	 *
	 * @return void
	 */
	public function testGetPatch() {
		foreach (self::ELOS as $elo) {
			$this->gg->getChampions($elo);
			$this->assertSame($this->gg->getPatch(), '8.10');
		}
	}

	/**
	 * Tests that the json() method serializes the ChampionGG's cached model to 
	 * the exact same JSON it was received as.
	 *
	 * @return void
	 */
	public function testJson() {
		foreach (self::ELOS as $elo) {
			$this->gg->getChampions($elo);
			$this->assertSame(
				$this->gg->json(),
				\file_get_contents(__DIR__ . "/data/ChampionGG/testGetChampions/{$elo}.json")	
			);
		}
	}
}