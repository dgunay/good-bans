<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Test\Mock\Lolalytics;
use GoodBans\ApiClient;
use PHPUnit\Framework\TestCase;

final class LolalyticsTest extends TestCase
{
	protected $lolalytics;

	public function setUp() {
		// $this->lolalytics = new Lolalytics(null, new );
	}

	/**
	 * Tests that getChampions() makes a request and decodes the result.
	 *
	 * @return void
	 */
	public function testGetChampions() {
		// foreach (array_keys(Lolalytics::ELO_URIS) as $elo) {
		// 	$result = $this->lolalytics->getChampions([$elo]);
		// 	// TODO: what do we want to test?
		// }		

		$this->markTestIncomplete();
	}
}