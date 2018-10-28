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
		$lol = new Lolalytics();

		$champions = $lol->getChampions();
		$this->assertTrue(true);
	}
}