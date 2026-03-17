<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Thrown when a network-level error occurs (DNS failure, connection refused, etc.).
 */
class NetworkException extends CloudLayerException
{
    public readonly string $requestPath;
    public readonly string $requestMethod;

    public function __construct(
        string $message,
        string $requestPath,
        string $requestMethod,
        ?\Throwable $previous = null,
    ) {
        $this->requestPath = $requestPath;
        $this->requestMethod = $requestMethod;
        parent::__construct($message, 0, $previous);
    }
}
