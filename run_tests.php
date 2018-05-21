<?php

echo passthru(
  str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/vendor/bin/phpunit --bootstrap ' . __DIR__ . '/vendor/autoload.php test --strict-coverage --stop-on-failure')
);