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
	public function __construct(string $api_key = null) {
		$this->key = $api_key;
	}

	protected function get(string $endpoint, array $args = []) : string {
		$elo = strtolower($args['elo']) ?? '';
		$response = @file_get_contents(
			__DIR__ . "/../data/ChampionGG/champions/{$elo}.json"
		);

		if ($response === false) {
			throw new \RuntimeException(\error_get_last()['message']);
		}

		return $response;
	}}