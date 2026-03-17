<?php

declare(strict_types=1);

namespace CloudLayer;

use CloudLayer\Types\ResponseHeaders;

/**
 * @internal Not part of the public API. May change without notice.
 *
 * Internal value object representing an HTTP response.
 */
final class HttpResponse
{
    /**
     * @param mixed $data Parsed response data (array for JSON, string for binary, null for 204)
     * @param int $status HTTP status code
     * @param array<string, list<string>> $headers Raw response headers
     * @param ResponseHeaders $cloudlayerHeaders Parsed cl-* headers
     * @param ?string $filename From Content-Disposition header
     */
    public function __construct(
        public mixed $data,
        public int $status,
        public array $headers,
        public ResponseHeaders $cloudlayerHeaders,
        public ?string $filename = null,
    ) {
    }
}
