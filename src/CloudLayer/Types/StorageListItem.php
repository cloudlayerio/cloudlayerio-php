<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final class StorageListItem
{
    public function __construct(
        public string $id,
        public string $title,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) ($data['id'] ?? ''),
            title: (string) ($data['title'] ?? ''),
        );
    }
}
