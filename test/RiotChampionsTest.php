<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\RiotChampions;
use PHPUnit\Framework\TestCase;

final class RiotChampionsTest extends TestCase
{
	/**
	 * @dataProvider getChampionsDataProvider 
	 */
	public function testGetChampions(string $version) {
		$riot = new Riotchampions();

		$expected = json_decode(
			file_get_contents(__DIR__ . "/data/RiotChampions/champion/{$version}.json"),
			true
		);
		$this->assertEquals($expected['data'], $riot->getChampions($version));
	}

	public function getChampionsDataProvider() {
		return array_map(function($version) {
			return [$version];
		}, $this->nonLolPatchVersions());
	}

	/**
	 * Returns all versions that aren't 'lolpatch_*'
	 * 
	 * @return array
	 */
	private function nonLolPatchVersions() : array {
		return array_filter(
			json_decode(
				file_get_contents(RiotChampions::VERSIONS_URI),
				true
			),
			function ($version) { return strpos($version, 'lolpatch') === false; }
		);
	}
}