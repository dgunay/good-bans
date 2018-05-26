<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

// TODO:
// use https://lolalytics.com/ranked/worldwide/current/platinum/plus/champions/
// current = current patch
class Lolalytics
{
  // Array of places to get data for each elo (last 7 days)
  const ELO_URIS = [
    'bronze'   => 'https://lolalytics.com/ranked/worldwide/bronze/champions/',
    'silver'   => 'https://lolalytics.com/ranked/worldwide/silver/champions/',
    'gold'     => 'https://lolalytics.com/ranked/worldwide/gold/champions/',
    'platinum' => 'https://lolalytics.com/ranked/worldwide/platinum/plus/champions/',
    'diamond'  => 'https://lolalytics.com/ranked/worldwide/diamond/plus/champions/',
  ];

  /** @var GuzzleHttp\Client */
  private $client;

  /** @var array */
  private $champions;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function scrape(array $elos = []) : array {
    if (empty($elo)) {
      $elo = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
    }

    foreach ($elos as $elo) {
      // TODO: use guzzle to get the thing
      throw new \Exception('not implemented');

      $this->parseDom($response);
    }
  }

  private function parseDom(string $html) : array {
    // TODO: parse the dom
  }
}