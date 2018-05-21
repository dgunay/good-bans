<?php

/**
 * Caches riots static datadragon champions API using the mock version cache
 */

require __DIR__ . '/../../../vendor/autoload.php';

use GoodBans\Test\Mock\RiotChampions;

$versions = json_decode(
	file_get_contents(RiotChampions::VERSIONS_URI),
	true
);

// filter out 'lolpatch_*' since they all 403.
$versions = array_filter($versions, function($vsn) {
	return strpos($vsn, 'lolpatch') === false;
});

$ch = \curl_init();
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
foreach ($versions as $version) {
	$fp_dl = fopen(__DIR__ . "/champion/$version.json", 'w');
	\curl_setopt(
		$ch,
		CURLOPT_URL, 
		"http://ddragon.leagueoflegends.com/cdn/$version/data/en_US/champion.json"
	);
	curl_setopt($ch, CURLOPT_FILE, $fp_dl);
	$response = curl_exec($ch);
}