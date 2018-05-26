<?php

namespace GoodBans\Test\Mock;

use GoodBans\Lolalytics as RealLolalytics;

/**
 * Just uses cached data scraped from Lolalytics
 */
class Lolalytics extends RealLolalytics
{
	const ELO_URIS = [
    'bronze'   => __DIR__ . '/../data/Lolalytics/bronze.html',
    'silver'   => __DIR__ . '/../data/Lolalytics/silver.html',
    'gold'     => __DIR__ . '/../data/Lolalytics/gold.html',
    'platinum' => __DIR__ . '/../data/Lolalytics/platinum.html',
    'diamond'  => __DIR__ . '/../data/Lolalytics/diamond.html',
  ];
}