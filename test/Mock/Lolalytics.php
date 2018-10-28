<?php

namespace GoodBans\Test\Mock;

use GoodBans\Lolalytics as RealLolalytics;
use GoodBans\ApiClient;

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
    'master'   => __DIR__ . '/../data/Lolalytics/master.html',
  ];

  public function __construct() {
    parent::__construct(new class extends ApiClient {
      public function get(string $endpoint, array $args = []) : string {
        return file_get_contents($endpoint); 
      }
    });
  }
}