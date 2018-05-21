<?php

print_r($argv);

foreach (array_slice($argv, 1) as $file) {
	$json = json_decode(file_get_contents($file), true);

	if (json_last_error() !== JSON_ERROR_NONE) {
		echo "$file had " . json_last_error_msg() . PHP_EOL;
	}
}