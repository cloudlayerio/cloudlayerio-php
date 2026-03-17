<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final class Cookie
{
    /**
     * @param 'Strict'|'Lax'|null $sameSite
     */
    public function __construct(
        public string $name,
        public string $value,
        public ?string $url = null,
        public ?string $domain = null,
        public ?string $path = null,
        public ?int $expires = null,
        public ?bool $httpOnly = null,
        public ?bool $secure = null,
        public ?string $sameSite = null,
    ) {
    }
}
