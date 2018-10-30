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

  /**
   * Sends a GET request. $args (associative) will be passed as url query 
   * parameters.
   *
   * @param string $endpoint
   * @param array $args
   * @return string
   */
  public function get(string $endpoint, array $args = []) : string {
    $response = @$this->client->request('GET', $endpoint, ['query' => $args]);
    $body = $response->getBody();
    return (string) $body; // must cast to string for testing
  }

  /**
   * POSTs to an endpoint. The body must be a string, encoded yourself (so if
   * you want json, use json_encode. If you want HTTP query string, use
   * http_build_query()).
   *
   * @param string $endpoint
   * @param string $body
   * @return string
   */
  public function post(string $endpoint, array $body = []) : string {
    $response = @$this->client->request('POST', $endpoint, ['form_params' => $body]);
    $body = $response->getBody();
    return (string) $body; // must cast to string for testing
  }
}