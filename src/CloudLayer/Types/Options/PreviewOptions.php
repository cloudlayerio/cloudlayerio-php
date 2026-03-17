<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

use CloudLayer\Types\ImageType;

final class PreviewOptions
{
    public function __construct(
        public ?int $width = null,
        public ?int $height = null,
        public ?ImageType $type = null,
        public int $quality = 80,
        public ?bool $maintainAspectRatio = null,
    ) {
    }
}
