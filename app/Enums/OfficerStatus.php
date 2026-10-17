<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum OfficerStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Suspended = 'suspended';
}
