<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum InvitationStatus: string
{
    use HasOptions;

    case Invited = 'invited';
    case Declined = 'declined';
    case Quoted = 'quoted';
}
