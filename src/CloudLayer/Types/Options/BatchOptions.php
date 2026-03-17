<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class BatchOptions
{
    /**
     * @param list<string> $urls Max 20 URLs
     */
    public function __construct(
        public array $urls,
    ) {
    }
}
