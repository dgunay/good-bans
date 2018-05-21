<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\RiotChampions;
use PHPUnit\Framework\TestCase;

final class RiotChampionsTest extends TestCase
{
	protected $riot;

	/**
	 * Tests that all versions in the versions API construct correctly.
	 *
	 * @dataProvider happyPathConstructorProvider
	 */
	public function testConstructHappyPath($case) {
		$riot = new RiotChampions($case);
		$this->assertObjectHasAttribute('champions', $riot);
		$this->assertObjectHasAttribute('patch', $riot);
	}

	public function happyPathConstructorProvider() {
		return array_map(
			function($case) {
				return [$case]; // array of arrays
			},
			json_decode(
				file_get_contents(RiotChampions::VERSIONS_URI),
				true
			)
		);
	}

	/**
	 * @dataProvider exceptionalConstructorProvider
	 */
	public function testConstructorExceptionalCases($case) {
		$this->expectException(\DomainException::class);
		$riot = new RiotChampions($case);
	}

	public function exceptionalConstructorProvider() {
		return [
			['version_that_doesnt_exist']
		];
	}
}