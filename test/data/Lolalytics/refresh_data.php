<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

use GoodBans\Lolalytics;

foreach (Lolalytics::ELO_URIS as $elo => $uri) {
	file_put_contents(
		__DIR__ . "/$elo.html",
		file_get_contents($uri)
	);
}