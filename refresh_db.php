<?php

/**
 * Repopulates the database with champions for later querying.
 * 
 * @author Devin Gunay <devingunay@gmail.com>
 */

require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/../config.php';

use GoodBans\ChampionGG;
use GoodBans\RiotChampions;
use GoodBans\ChampionsDatabaseRefresher;

$db = new ChampionsDatabaseRefresher(
	new \PDO('sqlite:' . __DIR__ . '/champions.db'),
	new ChampionGG($GLOBALS['champion.gg_key']),
	new RiotChampions('latest')
);

$db->refresh();