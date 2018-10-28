<?php

namespace GoodBans\Test\Mock;

use GoodBans\OpGG as RealOpGG;
use GoodBans\ApiClient;

/**
 * Just uses cached data scraped from op.gg
 */
class OpGG extends RealOpGG
{
	protected const PATCH_URI_PATTERN = __DIR__ . '/../data/ddragon/patch.json';

	// Mocks a client that just grabs local test fixture data
	public function __construct() {
		parent::__construct(new class extends ApiClient {
			public function post(string $endpoint, array $body = []) : string {
				// parse_str($body, $params); // decode url query params from the body
				if ($body['league'] === '') { $body['league'] = 'all'; } // can't have empty dirnames
				return file_get_contents(
					str_replace(
						'/', DIRECTORY_SEPARATOR,
						__DIR__ . "/../data/OpGG/{$body['type']}/{$body['league']}/data.html"
					)
				);
			}

			public function get(string $endpoint, array $args = []) : string {
        return file_get_contents($endpoint); 
      }
		});
	}

	// public function getPatch() : string {
	// 	$json = file_get_contents(__DIR__ . '/../data/ddragon/patch.json');
	// 	$json = json_decode($json, true);
  //   return $json;
	// }
}