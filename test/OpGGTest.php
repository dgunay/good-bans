<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\ApiClient;
use GoodBans\OpGG;
use PHPUnit\Framework\TestCase;

final class OpGGTest extends TestCase
{
	/**
	 * Tests that getChampions() makes a request and parses the HTML.
	 *
	 * @return void
	 */
	public function testGetChampions() {
    $gg = new OpGG();

    $this->markTestIncomplete();
	}
}