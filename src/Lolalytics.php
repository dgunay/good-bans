<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

// TODO:
// use https://lolalytics.com/ranked/worldwide/current/platinum/plus/champions/
// current = current patch
class Lolalytics extends ApiClient
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

  protected function refreshChampions() : array {
    $this->scrape();
    return $this->champions;
  }

  protected function scrape(array $elos = []) : array {
    if (empty($elo)) {
      $elo = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
    }

    $champions = [];

    foreach ($elos as $elo) {
      $html = $this->get(self::ELO_URIS[$elo]);
      $champions[$elo] = $this->parseDom($html);
    }

    $this->champions = $champions;
  }

  private function parseDom(string $html) : array {
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $champions = [];

    $rows = $xpath->query("//table[@id='championlist']//tr[td]");
    foreach ($rows as $row) {
      // TODO: the Champion object is too coupled to ChampionGG api to use here
      // Remake this with the Champion object later
      $champion = array(
        'id'       => null,
        'name'     => null,
        'winRate'  => null,
        'playRate' => null,
        'banRate'  => null,
      );
    
      // TODO: can we retrieve the mapping from somewhere else?
      $nodelist = $xpath->query("td[2]/div[@class='All']", $row);
      if ($nodelist->length > 0) {
        $champion['id'] = $nodelist->item(0)->nodeValue;
      }
      
      $nodelist = $xpath->query("td[2]/div[@class='All']", $row);
      if ($nodelist->length > 0) {
        $champion['name'] = $nodelist->item(0)->nodeValue;
      }
      
      $nodelist = $xpath->query("td[5]/div[@class='All']", $row);
      if ($nodelist->length > 0) {
        $champion['winRate'] = $nodelist->item(0)->nodeValue;
      }
      
      $nodelist = $xpath->query("td[6]/div[@class='All']", $row);
      if ($nodelist->length > 0) {
        $champion['playRate'] = $nodelist->item(0)->nodeValue;
      }
      
      $nodelist = $xpath->query("td[7]/div[@class='All']", $row);
      if ($nodelist->length > 0) {
        $champion['banRate'] = $nodelist->item(0)->nodeValue;
      }

      $champions[$champion['id']] = $champion;
    }

    return $champions;
  }

}