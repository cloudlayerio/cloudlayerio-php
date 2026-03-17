<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

use CloudLayer\Types\ApiErrorBody;

/**
 * Thrown when the API returns an HTTP 4xx/5xx error.
 */
class ApiException extends CloudLayerException
{
    public readonly int $status;
    public readonly string $statusText;
    public readonly mixed $body;
    public readonly string $requestPath;
    public readonly string $requestMethod;

    public function __construct(
        string $message,
        int $status,
        string $statusText,
        mixed $body,
        string $requestPath,
        string $requestMethod,
        ?\Throwable $previous = null,
    ) {
        $this->status = $status;
        $this->statusText = $statusText;
        $this->body = $body;
        $this->requestPath = $requestPath;
        $this->requestMethod = $requestMethod;
        parent::__construct($message, $status, $previous);
    }

    /**
     * Parse the response body into a structured ApiErrorBody, if possible.
     */
    public function getErrorBody(): ?ApiErrorBody
    {
        if (!is_array($this->body)) {
            return null;
        }

        return ApiErrorBody::fromArray($this->body);
    }
}
