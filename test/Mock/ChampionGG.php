<?php declare(strict_types=1);

namespace GoodBans\Test\Mock;

use GoodBans\ChampionGG as RealChampionGG;

/**
 * A mock version of the ChampionGG class that just uses local files to
 * simulate responses from the champion.gg API.
 * 
 * TODO: test for:
 *  - no malformed args are being sent
 *  - unexpected responses are dealt with gracefully
 */
class ChampionGG extends RealChampionGG
{
	protected function get(array $args = []) : string {
		// $response = $this->client->get(
		$response = file_get_contents(
			'file://' . __DIR__ . "/../data/ChampionGG/champions/{$args['elo']}.json"
		);
		return $response;
	}
}