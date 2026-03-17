<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class StorageRequestOptions
{
    public function __construct(
        public string $id,
    ) {
    }
}
