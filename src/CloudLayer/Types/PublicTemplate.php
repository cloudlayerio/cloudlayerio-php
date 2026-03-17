<?php

declare(strict_types=1);

namespace CloudLayer\Types;

final readonly class PublicTemplate
{
    /**
     * @param array<string, mixed> $extra Remaining dynamic fields
     */
    public function __construct(
        public string $id,
        public array $extra = [],
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $id = (string) ($data['id'] ?? '');
        $extra = $data;
        unset($extra['id']);

        return new self(id: $id, extra: $extra);
    }
}
