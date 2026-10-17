<?php

namespace App\Policies;

use App\Enums\BidEvaluationStatus;
use App\Enums\OrgRole;
use App\Enums\PrStatus;
use App\Models\BidEvaluation;
use App\Models\PurchaseRequisition;
use App\Models\User;

class BidEvaluationPolicy extends BasePolicy
{
    public function view(User $user, BidEvaluation $evaluation): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $pr = $evaluation->purchaseRequisition;

        if ($this->isAssignedOfficer($user, $pr)) {
            return true;
        }

        // Clients never see drafts.
        return $user->isClient()
            && $evaluation->status !== BidEvaluationStatus::Draft
            && $this->hasOrgRole($user, $pr->organization, ...OrgRole::cases());
    }

    /** authorize('create', [BidEvaluation::class, $pr]) */
    public function create(User $user, PurchaseRequisition $pr): bool
    {
        return in_array($pr->status, [PrStatus::Sourcing, PrStatus::BidEvaluation], true)
            && ($user->isAdmin() || $this->isAssignedOfficer($user, $pr));
    }

    /** Editable while draft, or after the client asked for changes (PR back in bid_evaluation). */
    public function update(User $user, BidEvaluation $evaluation): bool
    {
        $pr = $evaluation->purchaseRequisition;

        if (! ($user->isAdmin() || $this->isAssignedOfficer($user, $pr))) {
            return false;
        }

        return $evaluation->status === BidEvaluationStatus::Draft
            || ($evaluation->status === BidEvaluationStatus::Submitted && $pr->status === PrStatus::BidEvaluation);
    }

    public function submit(User $user, BidEvaluation $evaluation): bool
    {
        return $this->update($user, $evaluation);
    }

    /** Client approves or rejects the recommendation. */
    public function decide(User $user, BidEvaluation $evaluation): bool
    {
        if ($evaluation->status !== BidEvaluationStatus::Submitted) {
            return false;
        }

        return $user->isAdmin()
            || ($user->isClient()
                && $this->hasOrgRole($user, $evaluation->purchaseRequisition->organization, OrgRole::Owner, OrgRole::Approver));
    }
}
