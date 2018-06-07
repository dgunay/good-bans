<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

/** Handles requests to any external API or data source */
abstract class ApiClient
{
  /** @var array */
  protected $credentials;

  // TODO: should I bother with a sleep timer here?

  /** @var GuzzleHttp\Client */
  protected $client;

  public function __construct(Client $client) {
    $this->client = $client;
  }

  public function setCredentials(array $credentials) {
    $this->credentials = $credentials;
  }

  public function getCredentials() : array {
    return $this->credentials;
  }

  public function get(string $endpoint, array $args = []) : string {
    $response = $this->client->request('GET', $endpoint, ['query' => $args]);
    return $response->getBody();
  }
}