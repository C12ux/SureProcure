<?php

namespace App\Policies;

use App\Enums\AccreditationStatus;
use App\Enums\OrgRole;
use App\Models\User;
use App\Models\Vendor;

class VendorPolicy extends BasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin() || $this->isApprovedOfficer($user);
    }

    /** Clients never see vendor accreditation data directly. */
    public function view(User $user, Vendor $vendor): bool
    {
        return $user->isAdmin()
            || $this->isApprovedOfficer($user)
            || $this->isVendorMember($user, $vendor, ...OrgRole::cases());
    }

    /** A vendor edits its application only before it is submitted (or after a rejection). Admins always can. */
    public function update(User $user, Vendor $vendor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return in_array($vendor->accreditation_status, [AccreditationStatus::Pending, AccreditationStatus::Rejected], true)
            && $this->isVendorMember($user, $vendor, OrgRole::Owner, OrgRole::Approver);
    }

    /** Approve, request changes, reject. Admin only for now. */
    public function review(User $user, Vendor $vendor): bool
    {
        return $user->isAdmin();
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->isAdmin();
    }
}
