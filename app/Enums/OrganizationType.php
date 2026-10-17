<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum OrganizationType: string
{
    use HasOptions;

    case Client = 'client';
    case Vendor = 'vendor';
}
