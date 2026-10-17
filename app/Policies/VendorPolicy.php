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

    /** Vendor edits its own data, except while it is under review. */
    public function update(User $user, Vendor $vendor): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $vendor->accreditation_status !== AccreditationStatus::UnderReview
            && $this->isVendorMember($user, $vendor, OrgRole::Owner, OrgRole::Approver);
    }

    /** Accredit / reject. (Supplier accreditation is a service partners can offer.) */
    public function review(User $user, Vendor $vendor): bool
    {
        return $user->isAdmin() || $this->isApprovedOfficer($user);
    }

    public function delete(User $user, Vendor $vendor): bool
    {
        return $user->isAdmin();
    }
}
