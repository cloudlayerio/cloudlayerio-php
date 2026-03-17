<?php

declare(strict_types=1);

namespace CloudLayer\Types;

enum JobStatus: string
{
    case Pending = 'pending';
    case Success = 'success';
    case Error = 'error';
}
