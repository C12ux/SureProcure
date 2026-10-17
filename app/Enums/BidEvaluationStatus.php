<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum BidEvaluationStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Submitted = 'submitted';
    case Approved = 'approved';
    case Rejected = 'rejected';
}
