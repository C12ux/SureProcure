<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PoStatus: string
{
    use HasOptions;

    case Issued = 'issued';
    case Acknowledged = 'acknowledged';
    case Completed = 'completed';
    case Cancelled = 'cancelled';
}
