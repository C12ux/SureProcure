<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum QuotationStatus: string
{
    use HasOptions;

    case Received = 'received';
    case Shortlisted = 'shortlisted';
    case Rejected = 'rejected';
    case Awarded = 'awarded';
}
