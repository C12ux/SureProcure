<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum CriterionType: string
{
    use HasOptions;

    case Technical = 'technical';
    case Commercial = 'commercial';
}
