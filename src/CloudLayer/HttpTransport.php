<?php

declare(strict_types=1);

namespace CloudLayer;

use CloudLayer\Errors\ApiException;
use CloudLayer\Errors\AuthException;
use CloudLayer\Errors\NetworkException;
use CloudLayer\Errors\RateLimitException;
use CloudLayer\Errors\TimeoutException;
use CloudLayer\Types\ApiVersion;
use CloudLayer\Types\ResponseHeaders;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal Not part of the public API. May change without notice.
 *
 * Guzzle-based HTTP transport with retry logic, timeout handling, and error mapping.
 */
final class HttpTransport
{
    private Client $client;
    private ApiVersion $apiVersion;
    private int $maxRetries;
    private int $timeoutMs;

    /** @var callable(int): void */
    private $sleepFn;

    /**
     * @param array<string, string> $headers Additional headers to send with every request
     * @param ?callable(int): void $sleepFn Sleep function for testing (receives microseconds)
     */
    public function __construct(
        string $apiKey,
        string $baseUrl,
        ApiVersion $apiVersion,
        int $timeoutMs = 30000,
        int $maxRetries = 2,
        array $headers = [],
        ?HandlerStack $handler = null,
        ?callable $sleepFn = null,
    ) {
        $this->apiVersion = $apiVersion;
        $this->maxRetries = $maxRetries;
        $this->timeoutMs = $timeoutMs;
        $this->sleepFn = $sleepFn ?? usleep(...);

        $clientConfig = [
            'base_uri' => rtrim($baseUrl, '/'),
            'timeout' => $timeoutMs / 1000,
            'connect_timeout' => 10,
            'http_errors' => false,
            'headers' => array_merge(['X-API-Key' => $apiKey], $headers),
        ];

        if ($handler !== null) {
            $clientConfig['handler'] = $handler;
        }

        $this->client = new Client($clientConfig);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function get(string $path, array $options = []): HttpResponse
    {
        return $this->request('GET', $path, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function post(string $path, mixed $body = null, array $options = []): HttpResponse
    {
        if ($body !== null) {
            $options['body'] = $body;
        }

        return $this->request('POST', $path, $options);
    }

    /**
     * @param list<array{name: string, contents: string|resource, filename?: string}> $multipart
     * @param array<string, mixed> $options
     */
    public function postMultipart(string $path, array $multipart, array $options = []): HttpResponse
    {
        $options['multipart'] = $multipart;

        return $this->request('POST', $path, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    public function delete(string $path, array $options = []): HttpResponse
    {
        return $this->request('DELETE', $path, $options);
    }

    /**
     * @param array<string, mixed> $options
     */
    private function request(string $method, string $path, array $options = []): HttpResponse
    {
        $absolutePath = (bool) ($options['absolutePath'] ?? false);
        $retryable = (bool) ($options['retryable'] ?? false);
        $perRequestTimeout = $options['timeout'] ?? null;

        unset($options['absolutePath'], $options['retryable'], $options['timeout']);

        $url = $absolutePath
            ? $path
            : '/' . $this->apiVersion->value . $path;

        $guzzleOptions = $this->buildGuzzleOptions($method, $options, $perRequestTimeout);
        $maxAttempts = $retryable ? $this->maxRetries + 1 : 1;

        $lastException = null;

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            try {
                $response = $this->client->request($method, $url, $guzzleOptions);

                $status = $response->getStatusCode();

                if ($status >= 400) {
                    $this->throwForStatus($response, $method, $path);
                }

                return $this->parseResponse($response);
            } catch (ConnectException $e) {
                $lastException = $this->mapConnectException($e, $path, $method);
                // ConnectExceptions (timeout/network) are not retried
                throw $lastException;
            } catch (TransferException $e) {
                throw new NetworkException(
                    "Network error: {$e->getMessage()}",
                    $path,
                    $method,
                    $e,
                );
            } catch (RateLimitException $e) {
                $lastException = $e;
                if (!$retryable || $attempt >= $maxAttempts - 1) {
                    throw $e;
                }
                $delay = $e->retryAfter !== null
                    ? $e->retryAfter * 1_000_000
                    : $this->backoffMicroseconds($attempt);
                ($this->sleepFn)($delay);
            } catch (ApiException $e) {
                $lastException = $e;
                if (!$retryable || $e->status < 500 || $attempt >= $maxAttempts - 1) {
                    throw $e;
                }
                ($this->sleepFn)($this->backoffMicroseconds($attempt));
            }
        }

        // Should not reach here, but satisfy PHPStan
        throw $lastException ?? new NetworkException('Request failed', $path, $method); // @codeCoverageIgnore
    }

    private function parseResponse(ResponseInterface $response): HttpResponse
    {
        $status = $response->getStatusCode();
        $rawHeaders = $response->getHeaders();
        $cloudlayerHeaders = ResponseHeaders::fromHeaders($rawHeaders);
        $filename = $this->parseFilename($response);

        if ($status === 204) {
            return new HttpResponse(null, $status, $rawHeaders, $cloudlayerHeaders, $filename);
        }

        $body = (string) $response->getBody();
        $contentType = $response->getHeaderLine('Content-Type');

        if ($body === '' && $status !== 204) {
            return new HttpResponse('', $status, $rawHeaders, $cloudlayerHeaders, $filename);
        }

        $isJson = str_contains($contentType, 'application/json')
            || str_contains($contentType, 'text/json');

        if ($isJson) {
            $data = json_decode($body, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $data = $body;
            }
        } else {
            $data = $body;
        }

        return new HttpResponse($data, $status, $rawHeaders, $cloudlayerHeaders, $filename);
    }

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    private function buildGuzzleOptions(string $method, array $options, mixed $perRequestTimeout): array
    {
        $guzzleOptions = [];

        if ($perRequestTimeout !== null) {
            $guzzleOptions['timeout'] = (int) $perRequestTimeout / 1000;
        }

        // Query params
        if (isset($options['params']) && is_array($options['params'])) {
            $guzzleOptions['query'] = array_filter(
                $options['params'],
                static fn (mixed $v): bool => $v !== null,
            );
        }

        // Multipart upload
        if (isset($options['multipart']) && is_array($options['multipart'])) {
            $guzzleOptions['multipart'] = $options['multipart'];

            return $guzzleOptions;
        }

        // JSON body
        if (isset($options['body'])) {
            $guzzleOptions['json'] = $options['body'];
        }

        return $guzzleOptions;
    }

    /**
     * @throws ApiException|AuthException|RateLimitException
     */
    private function throwForStatus(ResponseInterface $response, string $method, string $path): never
    {
        $status = $response->getStatusCode();
        $statusText = $response->getReasonPhrase();
        $rawBody = (string) $response->getBody();

        $body = null;
        if ($rawBody !== '') {
            $decoded = json_decode($rawBody, true);
            $body = json_last_error() === JSON_ERROR_NONE ? $decoded : $rawBody;
        }

        // Auth errors
        if ($status === 401 || $status === 403) {
            throw new AuthException($status, $statusText, $body, $path, $method);
        }

        // Rate limit
        if ($status === 429) {
            $retryAfter = null;
            $retryAfterHeader = $response->getHeaderLine('Retry-After');
            if ($retryAfterHeader !== '') {
                $parsed = filter_var($retryAfterHeader, FILTER_VALIDATE_INT);
                $retryAfter = $parsed !== false ? $parsed : null;
            }

            throw new RateLimitException($status, $statusText, $body, $path, $method, $retryAfter);
        }

        // Extract message from body
        $message = is_array($body) && isset($body['message']) && is_string($body['message'])
            ? $body['message']
            : "API error: {$status} {$statusText}";

        throw new ApiException($message, $status, $statusText, $body, $path, $method);
    }

    private function mapConnectException(ConnectException $e, string $path, string $method): NetworkException|TimeoutException
    {
        $context = $e->getHandlerContext();
        $errno = $context['errno'] ?? 0;

        // CURLE_OPERATION_TIMEDOUT = 28
        if ($errno === 28) {
            return new TimeoutException($this->timeoutMs, $path, $method, $e);
        }

        return new NetworkException(
            "Network error: {$e->getMessage()}",
            $path,
            $method,
            $e,
        );
    }

    private function parseFilename(ResponseInterface $response): ?string
    {
        $disposition = $response->getHeaderLine('Content-Disposition');
        if ($disposition === '') {
            return null;
        }

        if (preg_match('/filename="?([^";\s]+)"?/', $disposition, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }

    private function backoffMicroseconds(int $attempt): int
    {
        $baseMs = (int) min(1000 * (2 ** $attempt), 4000);
        $jitterMs = random_int(0, 500);

        return ($baseMs + $jitterMs) * 1000; // convert to microseconds
    }
}
