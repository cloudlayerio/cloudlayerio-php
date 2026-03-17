<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\CloudLayer;
use CloudLayer\Errors\ConfigException;
use CloudLayer\Types\ApiVersion;
use PHPUnit\Framework\Attributes\Test;

final class CloudLayerTest extends TestCase
{
    #[Test]
    public function validConfigCreatesClient(): void
    {
        $client = $this->createMockClient([]);
        self::assertSame(ApiVersion::V2, $client->apiVersion);
        self::assertSame('https://api.cloudlayer.io', $client->baseUrl);
    }

    #[Test]
    public function emptyApiKeyThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('apiKey is required');
        new CloudLayer(apiKey: '', apiVersion: 'v2');
    }

    #[Test]
    public function whitespaceApiKeyThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        new CloudLayer(apiKey: '   ', apiVersion: 'v2');
    }

    #[Test]
    public function invalidApiVersionThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('apiVersion');
        new CloudLayer(apiKey: 'key', apiVersion: 'v3');
    }

    #[Test]
    public function stringApiVersionV1Accepted(): void
    {
        $client = $this->createMockClient([], 'v1');
        self::assertSame(ApiVersion::V1, $client->apiVersion);
    }

    #[Test]
    public function stringApiVersionV2Accepted(): void
    {
        $client = $this->createMockClient([], 'v2');
        self::assertSame(ApiVersion::V2, $client->apiVersion);
    }

    #[Test]
    public function enumApiVersionAccepted(): void
    {
        $client = new CloudLayer(
            apiKey: 'test',
            apiVersion: ApiVersion::V1,
            handler: \GuzzleHttp\HandlerStack::create(new \GuzzleHttp\Handler\MockHandler([])),
        );
        self::assertSame(ApiVersion::V1, $client->apiVersion);
    }

    #[Test]
    public function invalidBaseUrlThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('baseUrl');
        new CloudLayer(apiKey: 'key', apiVersion: 'v2', baseUrl: 'not-a-url');
    }

    #[Test]
    public function zeroTimeoutThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('timeout');
        new CloudLayer(apiKey: 'key', apiVersion: 'v2', timeout: 0);
    }

    #[Test]
    public function negativeTimeoutThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        new CloudLayer(apiKey: 'key', apiVersion: 'v2', timeout: -1);
    }

    #[Test]
    public function negativeMaxRetriesThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage('maxRetries');
        new CloudLayer(apiKey: 'key', apiVersion: 'v2', maxRetries: -1);
    }

    #[Test]
    public function maxRetriesAboveFiveThrowsConfigException(): void
    {
        $this->expectException(ConfigException::class);
        new CloudLayer(apiKey: 'key', apiVersion: 'v2', maxRetries: 6);
    }

    #[Test]
    public function defaultValuesApplied(): void
    {
        $client = $this->createMockClient([]);
        self::assertSame('https://api.cloudlayer.io', $client->baseUrl);
    }

    #[Test]
    public function readonlyPropertiesExposed(): void
    {
        $client = $this->createMockClient([], 'v1');
        self::assertSame(ApiVersion::V1, $client->apiVersion);
        self::assertSame('https://api.cloudlayer.io', $client->baseUrl);
    }
}
