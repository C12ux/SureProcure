<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum UserRole: string
{
    use HasOptions;

    case Client = 'client';
    case Vendor = 'vendor';
    case ProcurementOfficer = 'procurement_officer';
    case Admin = 'admin';
}
