<?php

namespace CloudLayerIO;

use CloudLayerIO\Exceptions\InvalidConfigException;
use CloudLayerIO\Exceptions\UnauthorizedUsage;
use CloudLayerIO\Exceptions\UninitializedClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\HandlerStack;


/**
 * @method static get(string $endpoint) *    Make get request to API and return response body
 * @method static post(string $endpoint, array $data) *    Make post request to API and return response body
 * @method static setHttpClient(\GuzzleHttp\Client $client) *    Change Http Client
 */
class Client
{
    const DEFAULT_BASE_URL = 'https://api.cloudlayer.io/v1/';

    private static array $supportedHttpMethods = [
        'get', 'post',
    ];

    private static ?self $instance = null;

    protected \GuzzleHttp\Client $httpClient;

    protected static ?int $rateLimit = null;

    protected static ?int $rateLimitRemaining = null;

    protected static ?string $rateLimitReset = null;


    public function __construct(string $baseUri, string $apiKey, ?HandlerStack $handlerStack = null)
    {
        $this->httpClient = new \GuzzleHttp\Client([
            'base_uri' => $baseUri,
            'headers' => [
                'x-api-key' => $apiKey,
            ],
            'handler' => $handlerStack,
        ]);
    }

    public static function config(array $config, ?HandlerStack $handlerStack = null): void
    {
        if (!isset($config['api_key'])) {
            throw new InvalidConfigException();
        }

        if (!isset($config['base_uri'])) {
            $config['base_uri'] = self::DEFAULT_BASE_URL;
        }

        self::$instance = new self($config['base_uri'], $config['api_key'], $handlerStack);
    }

    public static function getStatus(): bool
    {
        $response = json_decode(Client::get('getStatus')->getBody());
        return stripos($response->status, 'ok') === 0;
    }

    public static function __callStatic($name, $arguments)
    {
        if (!self::$instance) {
            throw new UninitializedClient();
        }

        if (in_array($name, self::$supportedHttpMethods)) {
            return self::$instance->request($name, ...$arguments);
        }

        throw new \Exception('Method Not found');

    }

    /**
     * @param string $method
     * @param string $endpoint
     * @param array $params
     * @return \Psr\Http\Message\ResponseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request(string $method, string $endpoint, array $params = []): \Psr\Http\Message\ResponseInterface
    {
        try {
            $response = $this->httpClient->request($method, $endpoint, $params);
            $this->setRateLimit($response->getHeaders());
            return $response;
        } catch (ClientException $e) {
            if ($e->getCode() == 401) {
                throw new UnauthorizedUsage($e);
            }
            throw $e;
        }
    }

    public static function resetClient()
    {
        self::$instance = null;
    }

    protected function setRateLimit(array $headers)
    {
        static::$rateLimit = isset($headers['X-RateLimit-Limit']) ? $headers['X-RateLimit-Limit'][0] : null;
        static::$rateLimitRemaining = isset($headers['X-RateLimit-Limit']) ? $headers['X-RateLimit-Limit'][0] : null;
        static::$rateLimitReset = isset($headers['X-RateLimit-Reset']) ? $headers['X-RateLimit-Reset'][0] : null;
    }

    /**
     * @return int|null
     */
    public static function getRateLimit(): ?int
    {
        return self::$rateLimit;
    }

    /**
     * @return int|null
     */
    public static function getRateLimitRemaining(): ?int
    {
        return self::$rateLimitRemaining;
    }

    /**
     * @return string|null
     */
    public static function getRateLimitReset(): ?string
    {
        return self::$rateLimitReset;
    }

}