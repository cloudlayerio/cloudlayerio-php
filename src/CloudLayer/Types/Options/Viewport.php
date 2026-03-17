<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final class Viewport
{
    public function __construct(
        public int $width,
        public int $height,
        public float $deviceScaleFactor = 1.0,
        public bool $isMobile = false,
        public bool $hasTouch = false,
        public bool $isLandscape = false,
    ) {
    }
}
