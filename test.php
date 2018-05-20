<?php

require 'vendor/autoload.php';

use GoodBans\Logger;
use Psr\Log\LogLevel;

$logger = new Logger(fopen('php://output', 'w'));

$logger->log(LogLevel::INFO, 'Test message!' . PHP_EOL);