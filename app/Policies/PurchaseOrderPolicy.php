<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Enums\PrStatus;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\User;

class PurchaseOrderPolicy extends BasePolicy
{
    public function view(User $user, PurchaseOrder $po): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $pr = $po->purchaseRequisition;

        if ($this->isAssignedOfficer($user, $pr)) {
            return true;
        }

        if ($user->isVendor()) {
            return $this->isVendorMember($user, $po->vendor, ...OrgRole::cases());
        }

        return $user->isClient() && $this->hasOrgRole($user, $pr->organization, ...OrgRole::cases());
    }

    /** authorize('create', [PurchaseOrder::class, $pr]) */
    public function create(User $user, PurchaseRequisition $pr): bool
    {
        return $pr->status === PrStatus::Approved
            && ($user->isAdmin() || $this->isAssignedOfficer($user, $pr));
    }

    public function update(User $user, PurchaseOrder $po): bool
    {
        return $user->isAdmin() || $this->isAssignedOfficer($user, $po->purchaseRequisition);
    }
}
