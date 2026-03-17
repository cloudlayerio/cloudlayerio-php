<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final class StatusResponse
{
    public function __construct(
        public string $status,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            status: (string) ($data['status'] ?? ''),
        );
    }
}
