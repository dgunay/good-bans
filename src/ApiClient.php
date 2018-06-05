<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

// TODO: wait and see how you can make the classes similar
abstract class ApiClient
{
  /** @var array */
  protected $credentials;

  /** @var GuzzleHttp\Client */
  protected $client;

  protected $champions = null;

  public function __construct(array $credentials, Client $client) {
    $this->credentials = $credentials;
    $this->client      = $client;
  }

  public function get(string $endpoint, array $args = []) {
    $response = $this->client->request('GET', $endpoint, ['query' => $args]);
    return $response->getBody();
  }

  // Call whatever functions necessary to get a fresh batch of champs.
  abstract protected function refreshChampions() : array;

  public function getChampions() : array {
    if ($this->champions)  {
      return $this->champions;
    }

    return $this->refreshChampions();
  }
}