<?php

namespace App\Models;

use App\Enums\OrgRole;
use Illuminate\Database\Eloquent\Relations\Pivot;

class OrganizationUser extends Pivot
{
    protected $table = 'organization_user';

    // The table has its own identity "id" column.
    public $incrementing = true;

    protected function casts(): array
    {
        return ['org_role' => OrgRole::class];
    }
}
