<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Thrown when a request or polling operation times out.
 */
class TimeoutException extends CloudLayerException
{
    public readonly int $timeout;
    public readonly string $requestPath;
    public readonly string $requestMethod;

    public function __construct(
        int $timeout,
        string $requestPath,
        string $requestMethod,
        ?\Throwable $previous = null,
    ) {
        $this->timeout = $timeout;
        $this->requestPath = $requestPath;
        $this->requestMethod = $requestMethod;
        parent::__construct(
            "Request timed out after {$timeout}ms: {$requestMethod} {$requestPath}",
            0,
            $previous,
        );
    }
}
