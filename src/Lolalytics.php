<?php declare(strict_types=1);

namespace GoodBans;

use GoodBans\ChampionsDataSource;

// TODO: should we scrape the patch number?
class Lolalytics extends ChampionsDataSource
{
  // Array of places to get data for each elo (last 7 days)
  const ELO_URIS = [
    'bronze'   => 'https://lolalytics.com/ranked/worldwide/bronze/champions/',
    'silver'   => 'https://lolalytics.com/ranked/worldwide/silver/champions/',
    'gold'     => 'https://lolalytics.com/ranked/worldwide/gold/champions/',
    'platinum' => 'https://lolalytics.com/ranked/worldwide/platinum/plus/champions/',
    'diamond'  => 'https://lolalytics.com/ranked/worldwide/diamond/plus/champions/',
  ];

  protected function refreshChampions(array $elos = []) : array {
    return $this->scrape($elos);
  }

  protected function scrape(array $elos = []) : array {
    if (empty($elos)) {
      $elos = ['bronze', 'silver', 'gold', 'platinum', 'diamond'];
    }

    $champions = [];

    foreach ($elos as $elo) {
      // FIXME: why does Guzzle emit so many warnings?
      $html = @$this->client->get(static::ELO_URIS[$elo]);
      $champions[$elo] = $this->parseDom($html);
    }

    $this->champions = $champions;
    return $champions;
  }

  private function parseDom(string $html) : array {
    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $champions = [];

    $rows = $xpath->query("//table[@id='championlist']//tr[td]");
    foreach ($rows as $row) {
      $champion = array(
        // 'id'       => null,
        'name'     => null,
        'winRate'  => null,
        'playRate' => null,
        'banRate'  => null,
      );
    
      // TODO: can we retrieve the ID mapping from somewhere else?
      $nodelist = $xpath->query("td[1]/div/@data-id", $row);
      if ($nodelist->length > 0) {
        $champion['championId'] = $nodelist->item(0)->nodeValue;
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

      $champions[$champion['championId']] = new Champion($champion, $champion['name']);
    }

    return $champions;
  }

  public function getPatch() : string { return ''; }
}