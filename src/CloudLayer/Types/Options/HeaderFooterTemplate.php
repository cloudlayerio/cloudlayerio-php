<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class HeaderFooterTemplate
{
    /**
     * @param 'template'|'extract'|null $method
     * @param array<string, string>|null $style
     * @param array<string, string>|null $imageStyle
     */
    public function __construct(
        public ?string $method = null,
        public ?string $selector = null,
        public ?Margin $margin = null,
        public ?array $style = null,
        public ?array $imageStyle = null,
        public ?string $template = null,
        public ?string $templateString = null,
    ) {
    }
}
