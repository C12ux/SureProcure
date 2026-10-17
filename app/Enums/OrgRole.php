<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum OrgRole: string
{
    use HasOptions;

    case Owner = 'owner';
    case Approver = 'approver';
    case Requester = 'requester';
    case Viewer = 'viewer';
}
