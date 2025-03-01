<?php

namespace SolomonOchepa\Nuban;

use Exception;
use GuzzleHttp\Client;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;

class Nuban
{
    private $api = null;

    private $url = null;

    public $client;

    private $params = [];

    public $enviroment;

    private string $access_token;

    public $cache_key = '';

    public $cache_ttl;

    public function __construct()
    {
        $this->cache_ttl = now()->tomorrow();

        $this->setKey();
        $this->setEndpoints();
        $this->resolve();

        if (empty($this->access_token)) {
            throw new \Exception('No Nuban token configured.');
        }
    }

    public function banks(bool $json = false)
    {
        $this->cache_key = 'banks'.($json ? ':json' : '');
        $this->url = $this->api.'/banks'.($json ? '-json' : '');
        $this->params = [];

        return $this->__execute('GET');
    }

    /**
     * Retrieve a specific bank by code
     */
    public function bank(string $code): ?string
    {
        $banks = $this->banks();

        return (in_array($code, array_keys($banks))) ? $banks[$code] : null;
    }

    public function account($number, $bank_code)
    {
        $this->cache_key = "accounts.{$bank_code}.{$number}";
        $this->url = $this->api.'/verify';
        $this->params = [
            'account_number' => $number,
            'bank_code' => $bank_code,
        ];

        return $this->__execute('GET');
    }

    public function resolve()
    {
        $this->client = new Client([
            'base_uri' => $this->api,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '.$this->getToken(),
            ],
        ]);
    }

    public function setKey(): void
    {
        $this->access_token = config('nuban.api_token');
    }

    public function setEndpoints(): void
    {
        $this->api = config('nuban.host');
    }

    public function timeout(): string
    {
        return config('nuban.options.request_timeout', 5);
    }

    protected function getToken()
    {
        $token = config('nuban.api_token');

        if (! $token) {
            throw_unless(app()->isProduction(), new Exception('No Nuban token configured.'));
        }

        return $token;
    }

    private function __execute(string $method = 'POST')
    {
        if (Cache::missing($this->cache_key)) {
            try {
                $options = [];

                if (! empty($this->params)) {
                    $options['query'] = array_filter($this->params);
                }

                $response = $this->client->request($method, $this->url, $options);
            } catch (\Throwable $e) {
                throw $e->getMessage();
            }

            if ($response->getBody()) {
                Cache::add($this->cache_key, Arr::sort(json_decode($response->getBody(), true)), $this->cache_ttl);
            } else {
                Cache::add($this->cache_key, Arr::sort(json_decode($response->getBody(), true)), now()->addSecond(10));
            }
        }

        return Cache::get($this->cache_key);
    }
}
