<?php

declare(strict_types=1);

namespace CloudLayer\Types\Options;

final readonly class Authentication
{
    public function __construct(
        public string $username,
        public string $password,
    ) {
    }
}
