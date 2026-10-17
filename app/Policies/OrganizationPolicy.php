<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $this->hasOrgRole($user, $organization, ...OrgRole::cases());
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Organization $organization): bool
    {
        return $user->isAdmin() || $this->hasOrgRole($user, $organization, OrgRole::Owner);
    }

    public function manageMembers(User $user, Organization $organization): bool
    {
        return $this->update($user, $organization);
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->isAdmin();
    }
}
