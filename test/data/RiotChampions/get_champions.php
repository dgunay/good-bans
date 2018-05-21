<?php

/**
 * Caches riots static datadragon champions API using the mock version cache
 */

require __DIR__ . '/../../../vendor/autoload.php';

use GoodBans\Test\Mock\RiotChampions;

$versions = json_decode(
	file_get_contents(RiotChampions::VERSIONS_URL),
	true
);

// filter out 'lolpatch_*' since they all 403.
$versions = array_filter($versions, function($vsn) {
	return strpos($vsn, 'lolpatch') === false;
});

foreach ($versions as $version) {
	$response = file_get_contents(
		"http://ddragon.leagueoflegends.com/cdn/{$version}/data/en_US/champion.json"
	);

	if ($response) {
		file_put_contents(__DIR__ . "/champion/$version.json", $response);
	}
}