<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Thrown when the API returns 429 Too Many Requests.
 */
class RateLimitException extends ApiException
{
    public readonly ?int $retryAfter;

    public function __construct(
        int $status,
        string $statusText,
        mixed $body,
        string $requestPath,
        string $requestMethod,
        ?int $retryAfter = null,
        ?\Throwable $previous = null,
    ) {
        $this->retryAfter = $retryAfter;

        $message = 'Rate limit exceeded.';
        if ($retryAfter !== null) {
            $message .= " Retry after {$retryAfter}s.";
        }

        parent::__construct($message, $status, $statusText, $body, $requestPath, $requestMethod, $previous);
    }
}
