<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Models\Subscription;
use App\Models\User;

class SubscriptionPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, Subscription $subscription): bool
    {
        return $user->isAdmin()
            || $this->hasOrgRole($user, $subscription->organization, OrgRole::Owner, OrgRole::Approver);
    }

    // Billing is invoiced manually for now, so only admins create or change subscriptions.
    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, Subscription $subscription): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Subscription $subscription): bool
    {
        return $user->isAdmin();
    }
}
