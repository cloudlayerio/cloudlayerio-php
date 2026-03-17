<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final class ListTemplatesOptions
{
    public function __construct(
        public ?string $type = null,
        public ?string $category = null,
        public ?string $tags = null,
        public ?bool $expand = null,
    ) {
    }
}
