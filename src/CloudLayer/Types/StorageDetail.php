<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final class StorageDetail
{
    public function __construct(
        public string $id,
        public string $title,
        public string $uid,
        public string $data,
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
            uid: (string) ($data['uid'] ?? ''),
            data: (string) ($data['data'] ?? ''),
        );
    }
}
