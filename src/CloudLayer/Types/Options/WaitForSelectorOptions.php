<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class WaitForSelectorOptions
{
    /**
     * @param ?array{visible?: bool, hidden?: bool, timeout?: int} $options
     */
    public function __construct(
        public string $selector,
        public ?array $options = null,
    ) {
    }
}
