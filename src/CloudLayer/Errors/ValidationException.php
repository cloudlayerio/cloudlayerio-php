<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Thrown when client-side input validation fails.
 */
class ValidationException extends CloudLayerException
{
    public readonly string $field;

    public function __construct(string $field, string $message, ?\Throwable $previous = null)
    {
        $this->field = $field;
        parent::__construct("Validation error on \"{$field}\": {$message}", 0, $previous);
    }
}
