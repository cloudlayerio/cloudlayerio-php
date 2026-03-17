<?php

declare(strict_types=1);

namespace CloudLayer\Types;

use CloudLayer\Errors\ConfigException;

/**
 * Immutable client configuration value object.
 */
final class Config
{
    public readonly string $apiKey;
    public readonly ApiVersion $apiVersion;
    public readonly string $baseUrl;
    public readonly int $timeout;
    public readonly int $maxRetries;
    /** @var array<string, string> */
    public readonly array $headers;

    /**
     * @param array<string, string> $headers
     */
    public function __construct(
        string $apiKey,
        ApiVersion $apiVersion,
        string $baseUrl = 'https://api.cloudlayer.io',
        int $timeout = 30000,
        int $maxRetries = 2,
        array $headers = [],
    ) {
        if (trim($apiKey) === '') {
            throw new ConfigException('apiKey is required and must be a non-empty string');
        }

        if (filter_var($baseUrl, FILTER_VALIDATE_URL) === false) {
            throw new ConfigException("baseUrl \"{$baseUrl}\" is not a valid URL");
        }

        if ($timeout <= 0) {
            throw new ConfigException('timeout must be a positive integer (milliseconds)');
        }

        if ($maxRetries < 0 || $maxRetries > 5) {
            throw new ConfigException('maxRetries must be an integer between 0 and 5');
        }

        $this->apiKey = $apiKey;
        $this->apiVersion = $apiVersion;
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->timeout = $timeout;
        $this->maxRetries = $maxRetries;
        $this->headers = $headers;
    }
}
