<?php

declare(strict_types=1);

namespace CloudLayer\Types;

enum ApiVersion: string
{
    case V1 = 'v1';
    case V2 = 'v2';
}
