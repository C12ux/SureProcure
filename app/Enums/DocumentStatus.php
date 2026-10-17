<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum DocumentStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case Verified = 'verified';
    case Rejected = 'rejected';
}
