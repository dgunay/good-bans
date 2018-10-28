<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\Champion;
use PHPUnit\Framework\TestCase;

final class ChampionTest extends TestCase
{
	/** @dataProvider namesProvider */
	public function testFixName(string $broken, string $fixed) {
		$this->assertEquals($fixed, Champion::fixName($broken));
	}

	public function namesProvider() {
		return [
			['ChoGath', 'Cho Gath'],
			['AurelionSol', 'Aurelion Sol'],
			['Le Blanc', 'LeBlanc'],
		];
	}
}