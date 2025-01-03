<?php

namespace SolomonOchepa\Nuban;

use GuzzleHttp\Client;
use SolomonOchepa\Nuban\Exceptions\ConfigurationException;

class Nuban
{
    private $APIEndpoint = null;

    private $url = null;

    public $client;

    private $params = [];

    public $enviroment;

    private string $accessToken;

    public function __construct()
    {
        $this->setKey();
        $this->setEndpoints();
        $this->resolve();

        if (empty($this->accessToken)) {
            throw new \Exception('No Nuban token configured.');
        }
    }

    public function account($number, $bank_code)
    {
        $this->url = $this->api . '/verify';
        $this->params = [
            'account_number' => $number,
            'bank_code' => $bank_code,
        ];

        return $this->__execute('GET');
    }

    public function resolve()
    {
        $this->client = new Client([
            'base_uri' => $this->APIEndpoint,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getToken(),
            ],
        ]);
    }

    public function setKey(): void
    {
        $this->accessToken = config('nuban.api_token');
    }

    public function setEndpoints(): void
    {
        $this->APIEndpoint = config('nuban.host');
    }

    /**
     * @return string
     */
    public function timeout()
    {
        return config('nuban.options.request_timeout', 5);
    }

    protected function getToken()
    {
        $token = config('nuban.api_token');

        if (! $token) {
            $this->throwUnlessProduction(
                new ConfigurationException('No Nuban token configured.')
            );
        }

        return $token;
    }

    private function __execute(string $requestType = 'POST')
    {
        $options = [];

        if (! empty($this->params)) {
            $options['query'] = array_filter($this->params);
        }

        $response = $this->client->request($requestType, $this->url, $options);

        return json_decode($response->getBody(), true);
    }
}
