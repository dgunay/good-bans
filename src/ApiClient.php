<?php declare(strict_types=1);

namespace GoodBans;

use GuzzleHttp\Client;

/** Handles requests to any external API or data source */
class ApiClient
{
  /** @var string */
  protected $credentials;

  // TODO: should I bother with a sleep timer here?

  /** @var GuzzleHttp\Client */
  protected $client;

  public function __construct(Client $client = null, string $credentials = '') {
    $this->client = $client ?? new Client();
    $this->credentials = $credentials;
  }

  public function setCredentials(string $credentials) {
    $this->credentials = $credentials;
  }

  public function getCredentials() : string {
    return $this->credentials;
  }

  public function get(string $endpoint, array $args = []) : string {
    // TODO: why does this cause problems for file:// calls?
    $response = @$this->client->request('GET', $endpoint, ['query' => $args]);
    $body = $response->getBody();
    return (string) $body; // must cast to string for testing
  }
}