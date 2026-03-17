<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final readonly class StorageNotAllowedResponse
{
    public function __construct(
        public bool $allowed,
        public string $reason,
        public int $statusCode,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            allowed: (bool) ($data['allowed'] ?? false),
            reason: (string) ($data['reason'] ?? ''),
            statusCode: (int) ($data['statusCode'] ?? 0),
        );
    }
}
