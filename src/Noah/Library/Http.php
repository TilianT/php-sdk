<?php
namespace Noah\Library;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Trait Http
 * @package Noah\Library
 */
Trait Http
{
    /**
     * guzzle client
     *
     * @Client
     */
    protected $client;

    /**
     * Set base API url.
     *
     * @param string $url
     */
    protected function setApiUrl(string $url): void
    {
        $this->client = new Client([
            'base_uri' => $url,
            'connect_timeout' => 15.0,
            'timeout' => 30.0,
        ]);
    }

    /**
     * http get request
     *
     * @param string $url
     * @param array|null $parameters
     * @return mixed
     * @throws \Exception
     */
    protected function get(string $url, array $parameters = null)
    {
        try {
            $response = $this->client->request('GET', $url, [
                'query' => $parameters
            ])->getBody();
        } catch (RequestException $exception) {
            throw $exception;
        }

        return json_decode($response);
    }

    /**
     * http post request
     *
     * @param string $url
     * @param array $parameters
     * @return mixed
     * @throws \Exception
     */
    protected function post(string $url, array $parameters = [])
    {
        try {
            $response = $this->client->request('POST', $url, [
                'json' => $parameters
            ])->getBody();
        } catch (RequestException $exception) {
            throw $exception;
        }

        return json_decode($response);
    }
}
