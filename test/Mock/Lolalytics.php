<?php

namespace GoodBans\Test\Mock;

use GoodBans\Lolalytics as RealLolalytics;

/**
 * Just uses cached data scraped from Lolalytics
 */
class Lolalytics extends RealLolalytics
{
	const ELO_URIS = [
    'bronze'   => 'file://' . __DIR__ . '/../data/Lolalytics/bronze.html',
    'silver'   => 'file://' . __DIR__ . '/../data/Lolalytics/silver.html',
    'gold'     => 'file://' . __DIR__ . '/../data/Lolalytics/gold.html',
    'platinum' => 'file://' . __DIR__ . '/../data/Lolalytics/platinum.html',
    'diamond'  => 'file://' . __DIR__ . '/../data/Lolalytics/diamond.html',
  ];
}