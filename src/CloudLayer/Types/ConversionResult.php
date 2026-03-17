<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Result from a conversion API call.
 *
 * For v2/async: $data is a Job instance.
 * For v1 sync: $data is a binary string.
 */
final readonly class ConversionResult
{
    public function __construct(
        public Job|string $data,
        public ResponseHeaders $headers,
        public int $status,
        public ?string $filename = null,
    ) {
    }
}
