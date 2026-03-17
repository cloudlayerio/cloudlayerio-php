<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class Margin
{
    /**
     * @param string|int|null $top CSS dimension (e.g., '1cm') or pixel integer
     * @param string|int|null $right CSS dimension or pixel integer
     * @param string|int|null $bottom CSS dimension or pixel integer
     * @param string|int|null $left CSS dimension or pixel integer
     */
    public function __construct(
        public string|int|null $top = null,
        public string|int|null $right = null,
        public string|int|null $bottom = null,
        public string|int|null $left = null,
    ) {
    }
}
