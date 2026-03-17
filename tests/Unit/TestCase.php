<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\CloudLayer;
use CloudLayer\HttpTransport;
use CloudLayer\Types\ApiVersion;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase as BaseTestCase;
use Psr\Http\Message\RequestInterface;

abstract class TestCase extends BaseTestCase
{
    /**
     * @param list<Response> $responses
     * @param list<array{request: RequestInterface}> $history
     */
    protected function createMockTransport(
        array $responses,
        array &$history = [],
        string $apiVersion = 'v2',
    ): HttpTransport {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $handlerStack->push(Middleware::history($history));

        return new HttpTransport(
            apiKey: 'test-api-key',
            baseUrl: 'https://api.cloudlayer.io',
            apiVersion: ApiVersion::from($apiVersion),
            timeoutMs: 30000,
            maxRetries: 2,
            handler: $handlerStack,
            sleepFn: static function (int $microseconds): void {
            },
        );
    }

    /**
     * @param list<Response> $responses
     */
    protected function createMockClient(
        array $responses,
        string $apiVersion = 'v2',
    ): CloudLayer {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);

        return new CloudLayer(
            apiKey: 'test-api-key',
            apiVersion: $apiVersion,
            handler: $handlerStack,
            sleepFn: static function (int $microseconds): void {
            },
        );
    }

    /**
     * @param list<array{request: RequestInterface}> $history
     */
    protected function getLastRequest(array &$history): RequestInterface
    {
        $last = end($history);
        self::assertIsArray($last);

        return $last['request'];
    }

    /**
     * @param array<string, mixed> $body
     * @param array<string, string> $headers
     */
    protected function mockJsonResponse(int $status, array $body, array $headers = []): Response
    {
        return new Response(
            $status,
            array_merge(['Content-Type' => 'application/json'], $headers),
            (string) json_encode($body),
        );
    }

    /**
     * @param array<string, string> $headers
     */
    protected function mockBinaryResponse(int $status, string $body, array $headers = []): Response
    {
        return new Response(
            $status,
            array_merge(['Content-Type' => 'application/pdf'], $headers),
            $body,
        );
    }

    protected function mockEmptyResponse(int $status = 204): Response
    {
        return new Response($status);
    }

    /**
     * @return array<string, mixed>
     */
    protected function loadFixture(string $name): array
    {
        $path = __DIR__ . '/../fixtures/' . $name;
        $content = file_get_contents($path);
        self::assertNotFalse($content);

        $data = json_decode($content, true);
        self::assertIsArray($data);

        return $data;
    }
}
