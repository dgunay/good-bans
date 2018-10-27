<?php declare(strict_types = 1);

namespace GoodBans\Test;

use GoodBans\OpGG;
use GoodBans\ApiClient;
use GoodBans\Champion;
use PHPUnit\Framework\TestCase;

final class OpGGTest extends TestCase
{
	/**
	 * Tests that getChampions() makes a request and parses the HTML.
	 *
	 * @dataProvider validDataProvider
	 * @return void
	 */
	public function testGetChampions(string $type, string $league) {
    $gg = new OpGG(
			new class extends ApiClient {
				public function post(string $endpoint, string $body = '') : string {
					parse_str($body, $params); // decode url query params from the body
					if ($params['league'] === '') { $params['league'] = 'all'; }
					return file_get_contents(
						__DIR__ . "/data/OpGG/{$params['type']}/{$params['league']}/data.html"
					);
				}
			}
		);
		$champs = $gg->getChampions([$league]);

		foreach ($champs as $champ) {
			$this->assertInstanceOf(Champion::class, $champ);
		}
    // $this->markTestIncomplete();
	}

	// public function testGetChampionsDoesntThrowExceptions() {

	// }

	// Gives paths to all <type>/<league>/data.html in OpGG fixture data folders
	public function validDataProvider() {
		$types = [];
		foreach (glob(__DIR__ . '/data/OpGG/*') as $file) {
			if (is_dir($file)) { $types[] = basename($file); }
		}

		$leagues = [];
		foreach (glob(__DIR__ . "/data/OpGG/{$types[0]}/*") as $file) {
			if (is_dir($file)) { $leagues[] = basename($file); }
		}
		
		$params = [];
		foreach ($types as $type) {
			foreach ($leagues as $league) {
				$params[] = [ $type, $league ];
			}
		}
		return $params;
	}
}