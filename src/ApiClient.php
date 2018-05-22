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

  public function __construct(array $credentials, Client $client) {
    $this->credentials = $credentials;
    $this->client      = $client;
  }

  public function get(string $endpoint, array $args = []) {
    $response = $this->client->request('GET', $endpoint, ['query' => $args]);
    return $response->getBody();
  }
}