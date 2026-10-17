<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Models\Organization;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Models\Vendor;

/**
 * Shared helpers. Laravel auto-discovers App\Policies\{Model}Policy,
 * so no registration is needed.
 */
abstract class BasePolicy
{
    /** Deactivated users are denied everything. Returning null continues to the real check. */
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_active === false ? false : null;
    }

    protected function hasOrgRole(User $user, ?Organization $organization, OrgRole ...$roles): bool
    {
        if (! $organization) {
            return false;
        }

        $role = $user->orgRole($organization);

        return $role !== null && in_array($role, $roles, true);
    }

    protected function isAssignedOfficer(User $user, PurchaseRequisition $pr): bool
    {
        return $user->isOfficer()
            && $pr->assigned_officer_id !== null
            && (int) $pr->assigned_officer_id === (int) $user->id;
    }

    protected function isApprovedOfficer(User $user): bool
    {
        return $user->isOfficer() && (bool) $user->officerProfile?->isApproved();
    }

    protected function isVendorMember(User $user, Vendor $vendor, OrgRole ...$roles): bool
    {
        return $user->isVendor() && $this->hasOrgRole($user, $vendor->organization, ...$roles);
    }
}
