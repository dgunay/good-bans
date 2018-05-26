<?php

/**
 * Repopulates the database with champions for later querying.
 * 
 * @author Devin Gunay <devingunay@gmail.com>
 */

require __DIR__ . '/vendor/autoload.php';

use GoodBans\ChampionGG;
use GoodBans\RiotChampions;
use GoodBans\ChampionsDatabase;
use GoodBans\BanRanker;

$pdo = new \PDO('sqlite:' . __DIR__ . '/champions.db'); 
$db = new ChampionsDatabase(
	$pdo,
	new ChampionGG($argv[1]),
	new RiotChampions('latest')
);

$db->refresh();

$br = new BanRanker($pdo);

print_r($br->topBans());