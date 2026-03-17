<?php

declare(strict_types=1);

namespace CloudLayer\Errors;

/**
 * Base exception for all CloudLayer SDK errors.
 */
class CloudLayerException extends \RuntimeException
{
    public function __construct(string $message, int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
