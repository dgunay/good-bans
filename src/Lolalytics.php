<?php declare(strict_types=1);

namespace GoodBans;

use GoodBans\ChampionsDataSource;

// TODO: should we scrape the patch number?
class Lolalytics extends ChampionsDataSource
{
  // Array of places to get data for each elo (last 7 days)
  public const ELO_URIS = [
    'bronze'   => 'https://lolalytics.com/ranked/worldwide/bronze/champions/',
    'silver'   => 'https://lolalytics.com/ranked/worldwide/silver/champions/',
    'gold'     => 'https://lolalytics.com/ranked/worldwide/gold/champions/',
    'platinum' => 'https://lolalytics.com/ranked/worldwide/platinum/plus/champions/',
    'diamond'  => 'https://lolalytics.com/ranked/worldwide/diamond/plus/champions/',
    'master'   => 'https://lolalytics.com/ranked/worldwide/master/plus/champions/',
  ];

  public function getElos(): array { return array_keys(self::ELO_URIS); }

  protected function refreshChampions(array $elos = []) : array {
    return $this->scrape($elos);
  }

  protected function scrape(array $elos = []) : array {
    if (empty($elos)) {
      $elos = array_keys(self::ELO_URIS);
    }

    $champions = [];

    foreach ($elos as $elo) {
      // FIXME: why does Guzzle emit so many warnings?
      $html = @$this->client->get(static::ELO_URIS[$elo]);

      // While we're at it, set patch
      if ($this->patch === null) {
        preg_match('/Patch (\d+\.\d+)/', $html, $match);
        if (isset($match[1])) {
          $this->patch = $match[1];
        }
        else {
          throw new \Exception("Failed to regex patch number.");
        }
      }

      // Get the JSON for champ data
      unset($match);
      preg_match('/var stats = ({.+?});/', $html, $match);
      if (isset($match[1])) {
        try {
          $champions[$elo] = $this->parseJson($match[1]);      
        }
        catch (\Exception $e) {
          throw new \Exception($e->getMessage() . ' from ' . static::ELO_URIS[$elo]);
        }
  
      }
      else {
        throw new \Exception("Failed to regex patch number.");
      }


      // Map it into an array of Champion
      $champions[$elo] = array_map(function(array $info) use ($elo) {
        $info['elo']   = $elo;
        $info['patch'] = $this->getPatch();
        return new Champion($info);
      }, $champions[$elo]);
    }

    $this->champions = $champions;
    return $champions;
  }

  private function parseDom(string $html) : array {
    if ($this->patch === null) {
      preg_match('/Patch (\d+\.\d+)/', $html, $match);
      if (isset($match[1])) {
        $this->patch = $match[1];
      }
      else {
        throw new \Exception("Failed to regex patch number.");
      }
    }

    unset($match);
    preg_match('/var stats = ({.+?});/', $html, $match);
    if (isset($match[1])) {
      $champ_data = $this->parseJson($match[1]);
    }
    else {
      throw new \Exception("Failed to regex patch number.");
    }

    $dom = new \DOMDocument();
    @$dom->loadHTML($html);
    $xpath = new \DOMXPath($dom);

    $champions = [];

    $rows = $xpath->query("//div[@class='table']/div[contains(@class,'row filter')]");
    if ($rows->count()) {
      foreach ($rows as $row) {
        $champion = array(
          // 'id'       => null,
          'name'     => null,
          'winRate'  => null,
          'playRate' => null,
          'banRate'  => null,
        );
        
        $xpaths = [
          'championId' => "td[1]/div/@data-id",
          'name'       => "td[2]/div[@class='All']",
          'winRate'    => "td[5]/div[@class='All']",
          'playRate'   => "td[6]/div[@class='All']",
          'banRate'    => "td[7]/div[@class='All']",
        ];
        foreach ($xpaths as $field => $xpath) {
          $nodelist = $xpath->query($xpath, $row);
          if ($nodelist->length > 0) {
            $champion[$field] = $nodelist->item(0)->nodeValue;
          }
          else {
            throw new \Exception("Failed to xpath '$field' using query '$xpath'.");
          }
        }
  
        $champions[$champion['name']] = $champion;
      }
    }
    else {
      throw new \Exception("Failed to xpath rows.");
    }

    return $champions;
  }

  protected function parseJson(string $json) : array {
    $data = json_decode($json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception("Failed to parse JSON");
    }

    // These are maps for how the JSON is structured.
    $lanes = [
      "Blank"   => 0,
      "Top"     => 1,
      "Jungle"  => 2,
      "Middle"  => 3,
      "ADC"     => 4,
      "Support" => 5,
    ];

    $maps = [
      'global' => [
        'gName'        => 0, 
        'gId'          => 1, 
        'gOrder'       => 2, 
        'gDefaultLane' => 3, 
        'gBan'         => 4, 
        'gTopWin'      => 5, 
        'gTopElo'      => 6, 
        'gTopRank'     => 7, 
        'gTopPick'     => 8,
      ],
      'lanes' => [
        'lWin'   => 0, 
        'lPick'  => 1, 
        'lTier'  => 2, 
        'lRank'  => 3, 
        'lGames' => 4, 
        'lPBI'   => 5, 
        // TODO: PBI (pick ban influence) is really useful, we could just steal it
        // and not have to do any processing to determine optimal bans.
      ],
    ];

    // FIXME: dead code that helped me visualize the data structure
    // foreach ($data as $ch_name => $champ) {
    //   $mangled = [

    //   ];
    //   foreach ($maps['global'] as $name => $idx) {
    //     $mangled['global'][$name] = $champ['global'][$idx];
    //   }
    //   $mangled['lanes'] = [];
    //   foreach ($champ['lanes'] as $lane_idx => $lane_data) {
    //     $mangled['lanes'][$lanes[$lane_idx]] = [];
    //     foreach ($maps['lanes'] as $name => $idx) {
    //       $mangled['lanes'][$lanes[$lane_idx]][$name] = $lane_data[$idx];
    //     }
    //   }

    //   $data[$ch_name] = $mangled;
    // }

    $ret = [];
    foreach ($data as $name => $stats) {
      $champ_data = [];
      $champ_data['name'] = $name;

      $champ_data['winRate']  = $stats['lanes'][$lanes['Blank']][$maps['lanes']['lWin']];
      $champ_data['playRate'] = $stats['lanes'][$lanes['Blank']][$maps['lanes']['lPick']];
      $champ_data['banRate']  = $stats['global'][$maps['global']['gBan']];

      $ret[$name] = $champ_data;
    }

    return $ret;
  }

  public function getPatch() : string { 
    if ($this->patch !== null) { return $this->patch; }

    throw new \RuntimeException(__CLASS__ . ' cannot call getPatch() before scrape()');
  }
}