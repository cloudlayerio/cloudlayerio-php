<?php

declare(strict_types=1);

namespace CloudLayer\Types;

enum ImageType: string
{
    case Jpg = 'jpg';
    case Jpeg = 'jpeg';
    case Png = 'png';
    case Svg = 'svg';
    case Webp = 'webp';
}
