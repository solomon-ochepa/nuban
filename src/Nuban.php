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

    public function getAccountDetails($accountNumber, $bankCode)
    {
        $this->url = $this->APIEndpoint . '/verify';
        $this->params = ['account_number' => $accountNumber, 'bank_code' => $bankCode];

        return $this->__execute('GET');
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
