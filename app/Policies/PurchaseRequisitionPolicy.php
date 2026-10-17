<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Enums\PrStatus;
use App\Models\Organization;
use App\Models\PurchaseRequisition;
use App\Models\User;

class PurchaseRequisitionPolicy extends BasePolicy
{
    /** Statuses an assigned officer may move a PR into. */
    private const OFFICER_TARGETS = [
        PrStatus::InReview,
        PrStatus::NeedsClarification,
        PrStatus::Sourcing,
        PrStatus::BidEvaluation,
        PrStatus::AwaitingClientReview,
        PrStatus::PoIssued,
        PrStatus::Closed,
    ];

    /** The list itself must be filtered with PurchaseRequisition::visibleTo($user). */
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, PurchaseRequisition $pr): bool
    {
        return PurchaseRequisition::visibleTo($user)->whereKey($pr->getKey())->exists();
    }

    /** authorize('create', [PurchaseRequisition::class, $organization]) */
    public function create(User $user, Organization $organization): bool
    {
        if (! $user->isClient()) {
            return false;
        }

        if (! $this->hasOrgRole($user, $organization, OrgRole::Owner, OrgRole::Approver, OrgRole::Requester)) {
            return false;
        }

        return ! config('sureprocure.require_subscription') || $organization->hasActiveSubscription();
    }

    /** Edit the request's content: only while it is a draft or the officer asked for clarification. */
    public function update(User $user, PurchaseRequisition $pr): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isClient()
            && in_array($pr->status, [PrStatus::Draft, PrStatus::NeedsClarification], true)
            && $this->clientCanAct($user, $pr);
    }

    public function delete(User $user, PurchaseRequisition $pr): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $user->isClient()
            && $pr->status === PrStatus::Draft
            && $this->clientCanAct($user, $pr);
    }

    public function restore(User $user, PurchaseRequisition $pr): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user, PurchaseRequisition $pr): bool
    {
        return false;
    }

    public function assign(User $user, PurchaseRequisition $pr): bool
    {
        return $user->isAdmin();
    }

    public function inviteVendor(User $user, PurchaseRequisition $pr): bool
    {
        return in_array($pr->status, [PrStatus::InReview, PrStatus::Sourcing], true)
            && ($user->isAdmin() || $this->isAssignedOfficer($user, $pr));
    }

    /** authorize('transition', [$pr, PrStatus::Sourcing]); also checks the workflow map. */
    public function transition(User $user, PurchaseRequisition $pr, PrStatus $to): bool
    {
        if (! $pr->status->canTransitionTo($to)) {
            return false;
        }

        if ($user->isAdmin()) {
            return true;
        }

        if ($this->isAssignedOfficer($user, $pr)) {
            return in_array($to, self::OFFICER_TARGETS, true);
        }

        return $user->isClient() && $this->clientMayTransition($user, $pr, $to);
    }

    // ---- internals ------------------------------------------------------

    /** Owner/approver of the org, or the original requester. */
    private function clientCanAct(User $user, PurchaseRequisition $pr): bool
    {
        $role = $user->orgRole($pr->organization);

        if ($role === null) {
            return false;
        }

        return in_array($role, [OrgRole::Owner, OrgRole::Approver], true)
            || ($role === OrgRole::Requester && (int) $pr->requested_by === (int) $user->id);
    }

    private function clientMayTransition(User $user, PurchaseRequisition $pr, PrStatus $to): bool
    {
        $isDecider = $this->hasOrgRole($user, $pr->organization, OrgRole::Owner, OrgRole::Approver);

        return match ($to) {
            PrStatus::Submitted => $pr->status === PrStatus::Draft && $this->clientCanAct($user, $pr),
            PrStatus::Cancelled => in_array($pr->status, [PrStatus::Draft, PrStatus::Submitted], true)
                && $this->clientCanAct($user, $pr),
            PrStatus::Approved, PrStatus::Rejected, PrStatus::NeedsClarification
                => $pr->status === PrStatus::AwaitingClientReview && $isDecider,
            default => false,
        };
    }
}
