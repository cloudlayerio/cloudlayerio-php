<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Thrown when the API returns 401 Unauthorized or 403 Forbidden.
 */
class AuthException extends ApiException
{
    public function __construct(
        int $status,
        string $statusText,
        mixed $body,
        string $requestPath,
        string $requestMethod,
        ?\Throwable $previous = null,
    ) {
        $message = match ($status) {
            401 => 'Authentication failed: invalid or missing API key',
            403 => 'Authorization failed: insufficient permissions',
            default => "Authentication error: {$status} {$statusText}",
        };

        parent::__construct($message, $status, $statusText, $body, $requestPath, $requestMethod, $previous);
    }
}
