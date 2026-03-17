<?php

declare(strict_types=1);

namespace CloudLayer\Tests\Unit;

use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\AuthException;
use CloudLayer\Errors\NetworkException;
use CloudLayer\Errors\RateLimitException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;

final class HttpTransportTest extends TestCase
{
    #[Test]
    public function getRequestSendsCorrectMethodAndPath(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, ['status' => 'ok']),
        ], $history);

        $http->get('/jobs');

        $request = $this->getLastRequest($history);
        self::assertSame('GET', $request->getMethod());
        self::assertStringContainsString('/v2/jobs', (string) $request->getUri());
    }

    #[Test]
    public function postRequestSendsJsonBody(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, ['id' => 'job-1']),
        ], $history);

        $http->post('/url/pdf', ['url' => 'https://example.com']);

        $request = $this->getLastRequest($history);
        self::assertSame('POST', $request->getMethod());
        self::assertStringContainsString('application/json', $request->getHeaderLine('Content-Type'));
    }

    #[Test]
    public function deleteRequestSendsCorrectMethod(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockEmptyResponse(),
        ], $history);

        $http->delete('/storage/123');

        $request = $this->getLastRequest($history);
        self::assertSame('DELETE', $request->getMethod());
    }

    #[Test]
    public function apiKeyHeaderAlwaysPresent(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, []),
        ], $history);

        $http->get('/jobs');

        $request = $this->getLastRequest($history);
        self::assertSame('test-api-key', $request->getHeaderLine('X-API-Key'));
    }

    #[Test]
    public function absolutePathSkipsVersionPrefix(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, []),
        ], $history);

        $http->get('/v2/templates', ['absolutePath' => true]);

        $request = $this->getLastRequest($history);
        $path = $request->getUri()->getPath();
        self::assertSame('/v2/templates', $path);
    }

    #[Test]
    public function normalPathPrependsVersion(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, []),
        ], $history);

        $http->get('/jobs');

        $request = $this->getLastRequest($history);
        $path = $request->getUri()->getPath();
        self::assertSame('/v2/jobs', $path);
    }

    #[Test]
    public function queryParamsAppendedCorrectly(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, []),
        ], $history);

        $http->get('/templates', ['params' => ['type' => 'invoice', 'null_param' => null], 'absolutePath' => true]);

        $request = $this->getLastRequest($history);
        $query = $request->getUri()->getQuery();
        self::assertStringContainsString('type=invoice', $query);
        self::assertStringNotContainsString('null_param', $query);
    }

    #[Test]
    public function jsonResponseParsedToArray(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, ['key' => 'value']),
        ]);

        $response = $http->get('/test');
        self::assertSame(['key' => 'value'], $response->data);
    }

    #[Test]
    public function binaryResponseReturnedAsString(): void
    {
        $http = $this->createMockTransport([
            $this->mockBinaryResponse(200, 'binary-pdf-data'),
        ]);

        $response = $http->get('/test');
        self::assertSame('binary-pdf-data', $response->data);
    }

    #[Test]
    public function noContentResponseReturnsNull(): void
    {
        $http = $this->createMockTransport([
            $this->mockEmptyResponse(204),
        ]);

        $response = $http->delete('/test');
        self::assertNull($response->data);
        self::assertSame(204, $response->status);
    }

    #[Test]
    public function clHeadersParsedIntoResponseHeaders(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, [], [
                'cl-worker-job-id' => 'job-123',
                'cl-process-time' => '1500',
                'cl-bandwidth-cost' => '0.001',
            ]),
        ]);

        $response = $http->get('/test');
        self::assertSame('job-123', $response->cloudlayerHeaders->workerJobId);
        self::assertSame(1500, $response->cloudlayerHeaders->processTime);
        self::assertSame(0.001, $response->cloudlayerHeaders->bandwidthCost);
    }

    #[Test]
    public function contentDispositionFilenameParsed(): void
    {
        $http = $this->createMockTransport([
            new Response(200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="report.pdf"',
            ], 'pdf-data'),
        ]);

        $response = $http->get('/test');
        self::assertSame('report.pdf', $response->filename);
    }

    #[Test]
    public function status401ThrowsAuthException(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(401, ['message' => 'Unauthorized']),
        ]);

        $this->expectException(AuthException::class);
        $http->get('/test');
    }

    #[Test]
    public function status403ThrowsAuthException(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(403, ['message' => 'Forbidden']),
        ]);

        $this->expectException(AuthException::class);
        $http->get('/test');
    }

    #[Test]
    public function status429ThrowsRateLimitExceptionWithRetryAfter(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(429, ['message' => 'Rate limited'], ['Retry-After' => '30']),
        ]);

        try {
            $http->get('/test');
            self::fail('Expected RateLimitException');
        } catch (RateLimitException $e) {
            self::assertSame(30, $e->retryAfter);
        }
    }

    #[Test]
    public function status500ThrowsApiException(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(500, ['message' => 'Internal error']),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionMessage('Internal error');
        $http->get('/test');
    }

    #[Test]
    public function errorBodyMessageExtracted(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(400, ['message' => 'Invalid parameter']),
        ]);

        try {
            $http->get('/test');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('Invalid parameter', $e->getMessage());
        }
    }

    #[Test]
    public function errorBodyWithoutMessageUsesGeneric(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(422, ['error' => 'validation']),
        ]);

        try {
            $http->get('/test');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertStringContainsString('API error: 422', $e->getMessage());
        }
    }

    #[Test]
    public function retryOn429WithRetryableTrue(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(429, ['message' => 'Rate limited'], ['Retry-After' => '1']),
            $this->mockJsonResponse(200, ['ok' => true]),
        ]);

        $response = $http->get('/test', ['retryable' => true]);
        self::assertSame(['ok' => true], $response->data);
    }

    #[Test]
    public function retryOn500WithRetryableTrue(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(500, ['message' => 'error']),
            $this->mockJsonResponse(200, ['ok' => true]),
        ]);

        $response = $http->get('/test', ['retryable' => true]);
        self::assertSame(['ok' => true], $response->data);
    }

    #[Test]
    public function noRetryOn429WithoutRetryable(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(429, ['message' => 'Rate limited']),
        ]);

        $this->expectException(RateLimitException::class);
        $http->get('/test');
    }

    #[Test]
    public function noRetryOn400EvenWithRetryable(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(400, ['message' => 'Bad request']),
        ]);

        $this->expectException(ApiException::class);
        $http->get('/test', ['retryable' => true]);
    }

    #[Test]
    public function emptyBodyWith200ReturnsEmptyString(): void
    {
        $http = $this->createMockTransport([
            new Response(200, [], ''),
        ]);

        $response = $http->get('/test');
        self::assertSame('', $response->data);
    }

    #[Test]
    public function malformedJsonErrorBodyStoredAsString(): void
    {
        $http = $this->createMockTransport([
            new Response(500, ['Content-Type' => 'text/plain'], 'not json at all'),
        ]);

        try {
            $http->get('/test');
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            self::assertSame('not json at all', $e->body);
        }
    }

    #[Test]
    public function absentContentTypeHeaderTreatedAsBinary(): void
    {
        $http = $this->createMockTransport([
            new Response(200, [], 'raw-binary-data'),
        ]);

        $response = $http->get('/test');
        self::assertSame('raw-binary-data', $response->data);
    }

    #[Test]
    public function connectExceptionThrowsNetworkException(): void
    {
        $http = $this->createMockTransport([
            new ConnectException(
                'Connection refused',
                new Request('GET', '/test'),
                null,
                ['errno' => 7],
            ),
        ]);

        $this->expectException(NetworkException::class);
        $http->get('/test');
    }

    #[Test]
    public function maxRetriesCapsAttempts(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(500, ['message' => 'error']),
            $this->mockJsonResponse(500, ['message' => 'error']),
            $this->mockJsonResponse(500, ['message' => 'error']),
        ], $history);

        try {
            $http->get('/test', ['retryable' => true]);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            // maxRetries=2 means 3 total attempts (1 initial + 2 retries)
            self::assertCount(3, $history);
        }
    }

    #[Test]
    public function retryExhaustionThrowsFinalError(): void
    {
        $http = $this->createMockTransport([
            $this->mockJsonResponse(502, ['message' => 'Bad Gateway']),
            $this->mockJsonResponse(503, ['message' => 'Service Unavailable']),
            $this->mockJsonResponse(504, ['message' => 'Gateway Timeout']),
        ]);

        try {
            $http->get('/test', ['retryable' => true]);
            self::fail('Expected ApiException');
        } catch (ApiException $e) {
            // Last error is the one thrown
            self::assertSame(504, $e->status);
        }
    }

    #[Test]
    public function multipleQueuedResponsesProcessedInOrder(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, ['first' => true]),
            $this->mockJsonResponse(200, ['second' => true]),
        ], $history);

        $r1 = $http->get('/first');
        $r2 = $http->get('/second');

        self::assertSame(['first' => true], $r1->data);
        self::assertSame(['second' => true], $r2->data);
        self::assertCount(2, $history);
    }

    #[Test]
    public function timeoutConnectExceptionMapsToTimeoutException(): void
    {
        $http = $this->createMockTransport([
            new ConnectException(
                'Connection timed out',
                new Request('POST', '/url/pdf'),
                null,
                ['errno' => 28], // CURLE_OPERATION_TIMEDOUT
            ),
        ]);

        try {
            $http->post('/url/pdf', ['url' => 'https://example.com']);
            self::fail('Expected TimeoutException');
        } catch (\CloudLayer\Errors\TimeoutException $e) {
            self::assertSame(30000, $e->timeout);
            self::assertSame('/url/pdf', $e->requestPath);
        }
    }

    #[Test]
    public function multipartDoesNotSendJsonContentType(): void
    {
        $history = [];
        $http = $this->createMockTransport([
            $this->mockJsonResponse(200, ['id' => 'job-1']),
        ], $history);

        $http->postMultipart('/docx/pdf', [
            ['name' => 'file', 'contents' => 'fake-file-content', 'filename' => 'test.docx'],
        ]);

        $request = $this->getLastRequest($history);
        $contentType = $request->getHeaderLine('Content-Type');
        self::assertStringContainsString('multipart/form-data', $contentType);
        self::assertStringNotContainsString('application/json', $contentType);
    }
}
