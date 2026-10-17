<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum AccreditationStatus: string
{
    use HasOptions;

    case Pending = 'pending';
    case UnderReview = 'under_review';
    case Accredited = 'accredited';
    case Rejected = 'rejected';
    case Expired = 'expired';
    case Suspended = 'suspended';
}
