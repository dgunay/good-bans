<?php

require 'vendor/autoload.php';

use GoodBans\BanRanker;

$br = new BanRanker(
  new \PDO('sqlite:' . __DIR__ . '/champions.db')
);

echo json_encode($br->topBans());