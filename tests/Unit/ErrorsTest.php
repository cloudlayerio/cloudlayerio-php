<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\AuthException;
use CloudLayer\Errors\CloudLayerException;
use CloudLayer\Errors\ConfigException;
use CloudLayer\Errors\NetworkException;
use CloudLayer\Errors\RateLimitException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Errors\ValidationException;
use PHPUnit\Framework\Attributes\Test;

final class ErrorsTest extends TestCase
{
    #[Test]
    public function cloudLayerExceptionExtendsRuntimeException(): void
    {
        $e = new CloudLayerException('test');
        self::assertInstanceOf(\RuntimeException::class, $e);
    }

    #[Test]
    public function configExceptionExtendsCloudLayerException(): void
    {
        $e = new ConfigException('test');
        self::assertInstanceOf(CloudLayerException::class, $e);
    }

    #[Test]
    public function apiExceptionContextProperties(): void
    {
        $e = new ApiException('msg', 500, 'Internal Server Error', ['error' => 'fail'], '/test', 'POST');
        self::assertSame(500, $e->status);
        self::assertSame('Internal Server Error', $e->statusText);
        self::assertSame(['error' => 'fail'], $e->body);
        self::assertSame('/test', $e->requestPath);
        self::assertSame('POST', $e->requestMethod);
    }

    #[Test]
    public function authExceptionMessage401(): void
    {
        $e = new AuthException(401, 'Unauthorized', null, '/test', 'GET');
        self::assertSame('Authentication failed: invalid or missing API key', $e->getMessage());
        self::assertInstanceOf(ApiException::class, $e);
    }

    #[Test]
    public function authExceptionMessage403(): void
    {
        $e = new AuthException(403, 'Forbidden', null, '/test', 'GET');
        self::assertSame('Authorization failed: insufficient permissions', $e->getMessage());
    }

    #[Test]
    public function rateLimitExceptionWithRetryAfter(): void
    {
        $e = new RateLimitException(429, 'Too Many Requests', null, '/test', 'GET', 30);
        self::assertSame(30, $e->retryAfter);
        self::assertStringContainsString('Retry after 30s', $e->getMessage());
        self::assertInstanceOf(ApiException::class, $e);
    }

    #[Test]
    public function rateLimitExceptionWithoutRetryAfter(): void
    {
        $e = new RateLimitException(429, 'Too Many Requests', null, '/test', 'GET');
        self::assertNull($e->retryAfter);
        self::assertSame('Rate limit exceeded.', $e->getMessage());
    }

    #[Test]
    public function timeoutExceptionProperties(): void
    {
        $e = new TimeoutException(30000, '/url/pdf', 'POST');
        self::assertSame(30000, $e->timeout);
        self::assertSame('/url/pdf', $e->requestPath);
        self::assertSame('POST', $e->requestMethod);
        self::assertStringContainsString('30000ms', $e->getMessage());
        self::assertInstanceOf(CloudLayerException::class, $e);
    }

    #[Test]
    public function networkExceptionPreservesPrevious(): void
    {
        $original = new \RuntimeException('connection refused');
        $e = new NetworkException('Network error', '/test', 'GET', $original);
        self::assertSame($original, $e->getPrevious());
        self::assertSame('/test', $e->requestPath);
        self::assertInstanceOf(CloudLayerException::class, $e);
    }

    #[Test]
    public function validationExceptionFieldAndMessage(): void
    {
        $e = new ValidationException('url', 'must be a valid URL');
        self::assertSame('url', $e->field);
        self::assertSame('Validation error on "url": must be a valid URL', $e->getMessage());
        self::assertInstanceOf(CloudLayerException::class, $e);
    }

    #[Test]
    public function apiExceptionGetErrorBodyWithArray(): void
    {
        $e = new ApiException('msg', 400, 'Bad Request', ['message' => 'invalid', 'statusCode' => 400], '/test', 'POST');
        $body = $e->getErrorBody();
        self::assertNotNull($body);
        self::assertSame('invalid', $body->message);
        self::assertSame(400, $body->statusCode);
    }

    #[Test]
    public function apiExceptionGetErrorBodyWithStringReturnsNull(): void
    {
        $e = new ApiException('msg', 500, 'Error', 'raw text', '/test', 'GET');
        self::assertNull($e->getErrorBody());
    }

    #[Test]
    public function apiExceptionGetErrorBodyWithNullReturnsNull(): void
    {
        $e = new ApiException('msg', 500, 'Error', null, '/test', 'GET');
        self::assertNull($e->getErrorBody());
    }
}
