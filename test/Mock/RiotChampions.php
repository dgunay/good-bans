<?php declare(strict_types=1);

namespace GoodBans\Test\Mock;

use GoodBans\RiotChampions as RealRiotChampions;

/**
 * Uses a locally cached response of Riot's static datadragon API.
 */
class RiotChampions extends RealRiotChampions
{
	// // JSON array of valid DataDragon versions
	const VERSIONS_URI = __DIR__ . '/../data/RiotChampions/versions.json';

	// URI for the fixtures, used in sprintf()
	const FILE_URI_PATTERN = __DIR__ . "/../data/RiotChampions/champion/%s.json";
}