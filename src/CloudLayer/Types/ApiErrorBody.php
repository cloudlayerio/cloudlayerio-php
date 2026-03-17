<?php

declare(strict_types=1);

namespace CloudLayer\Types;

/**
 * Parsed error response body from the CloudLayer API.
 */
final class ApiErrorBody
{
    /**
     * @param list<string>|null $details
     */
    public function __construct(
        public ?int $statusCode = null,
        public ?string $error = null,
        public ?string $message = null,
        public ?array $details = null,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $details = null;
        if (isset($data['details']) && is_array($data['details'])) {
            $details = array_map(strval(...), array_values($data['details']));
        }

        return new self(
            statusCode: isset($data['statusCode']) ? (int) $data['statusCode'] : null,
            error: isset($data['error']) ? (string) $data['error'] : null,
            message: isset($data['message']) ? (string) $data['message'] : null,
            details: $details,
        );
    }
}
