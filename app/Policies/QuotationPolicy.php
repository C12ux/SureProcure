<?php

namespace App\Policies;

use App\Enums\InvitationStatus;
use App\Enums\OrgRole;
use App\Enums\PrStatus;
use App\Models\PurchaseRequisition;
use App\Models\Quotation;
use App\Models\User;
use App\Models\Vendor;

class QuotationPolicy extends BasePolicy
{
    /** Clients see raw quotations only once the evaluation reaches them. */
    private const CLIENT_VISIBLE = [
        PrStatus::AwaitingClientReview,
        PrStatus::Approved,
        PrStatus::PoIssued,
        PrStatus::Closed,
    ];

    public function view(User $user, Quotation $quotation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $pr = $quotation->purchaseRequisition;

        if ($this->isAssignedOfficer($user, $pr)) {
            return true;
        }

        if ($user->isVendor()) {
            return $this->isVendorMember($user, $quotation->vendor, ...OrgRole::cases());
        }

        return $user->isClient()
            && in_array($pr->status, self::CLIENT_VISIBLE, true)
            && $this->hasOrgRole($user, $pr->organization, ...OrgRole::cases());
    }

    /** authorize('create', [Quotation::class, $pr, $vendor]) */
    public function create(User $user, PurchaseRequisition $pr, Vendor $vendor): bool
    {
        if ($pr->status !== PrStatus::Sourcing || ! $vendor->isAccredited()) {
            return false;
        }

        $invited = $pr->invitations()
            ->where('vendor_id', $vendor->id)
            ->where('status', '!=', InvitationStatus::Declined->value)
            ->exists();

        if (! $invited) {
            return false;
        }

        // Officers/admins may encode a quotation on the vendor's behalf.
        return $user->isAdmin()
            || $this->isAssignedOfficer($user, $pr)
            || $this->isVendorMember($user, $vendor, OrgRole::Owner, OrgRole::Approver);
    }

    public function update(User $user, Quotation $quotation): bool
    {
        $pr = $quotation->purchaseRequisition;

        if ($pr->status !== PrStatus::Sourcing) {
            return false;
        }

        if ($user->isAdmin() || $this->isAssignedOfficer($user, $pr)) {
            return true;
        }

        return $this->isVendorMember($user, $quotation->vendor, OrgRole::Owner, OrgRole::Approver);
    }

    public function delete(User $user, Quotation $quotation): bool
    {
        $pr = $quotation->purchaseRequisition;

        return $pr->status === PrStatus::Sourcing
            && ($user->isAdmin() || $this->isAssignedOfficer($user, $pr));
    }

    /** Shortlist / reject / award. */
    public function manage(User $user, Quotation $quotation): bool
    {
        return $user->isAdmin() || $this->isAssignedOfficer($user, $quotation->purchaseRequisition);
    }
}
