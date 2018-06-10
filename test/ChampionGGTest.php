<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\ChampionGG;
use GoodBans\ApiClient;
use PHPUnit\Framework\TestCase;

final class ChampionGGTest extends TestCase
{
	protected $gg;

	public function setUp() {
		$this->gg = new ChampionGG(
			new ApiClient()
		);
	}

	/**
	 * Tests that getChampions() makes a request and decodes the json.
	 *
	 * @return void
	 */
	public function testGetChampions() {
		foreach (ChampionGG::ELOS as $elo) {
			$result = $this->gg->getChampions([$elo]);
			
			print_r($result['bronze']['266']); exit;

			// TODO: is there a more effective way to test this?
			$this->assertSame(
				$result,
				json_decode(
					file_get_contents(
						__DIR__ . "/data/ChampionGG/champions/{$elo}.json"
					),
					true
				)
			);
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
				\file_get_contents(__DIR__ . "/data/ChampionGG/champions/{$elo}.json")	
			);
		}
	}
}