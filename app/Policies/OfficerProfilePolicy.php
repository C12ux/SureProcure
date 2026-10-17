<?php

namespace App\Policies;

use App\Enums\OfficerStatus;
use App\Models\OfficerProfile;
use App\Models\User;

class OfficerProfilePolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, OfficerProfile $profile): bool
    {
        return $user->isAdmin() || $this->isOwn($user, $profile);
    }

    public function update(User $user, OfficerProfile $profile): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->isOwn($user, $profile)
            && in_array($profile->accreditation_status, [OfficerStatus::Pending, OfficerStatus::Rejected], true);
    }

    public function review(User $user, OfficerProfile $profile): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, OfficerProfile $profile): bool
    {
        return $user->isAdmin();
    }

    private function isOwn(User $user, OfficerProfile $profile): bool
    {
        return (int) $profile->user_id === (int) $user->id;
    }
}
