<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

/** Handles requests to any external API or data source */
abstract class ApiClient
{
  /** @var array|null */
  protected $credentials;

  /** @var GuzzleHttp\Client */
  protected $client;

  public function __construct(Client $client, array $credentials = null) {
    $this->credentials = $credentials;
    $this->client      = $client;
  }

  public function getCredentials() {
    return $this->credentials;
  }

  public function get(string $endpoint, array $args = []) : string {
    $response = $this->client->request('GET', $endpoint, ['query' => $args]);
    return $response->getBody();
  }
}