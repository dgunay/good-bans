<?php

echo passthru(
  __DIR__ . '/vendor/bin/phpunit --bootstrap ' . __DIR__ . '/vendor/autoload.php test'
);