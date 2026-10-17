<?php

namespace App\Policies;

use App\Enums\OrgRole;
use App\Enums\PaymentStatus;
use App\Models\Payment;
use App\Models\Subscription;
use App\Models\User;

class PaymentPolicy extends BasePolicy
{
    public function view(User $user, Payment $payment): bool
    {
        return $user->isAdmin() || $this->isBillingContact($user, $payment->subscription);
    }

    /** Submit a payment (with proof) against a subscription: authorize('create', [Payment::class, $subscription]) */
    public function create(User $user, Subscription $subscription): bool
    {
        return $user->isAdmin() || $this->isBillingContact($user, $subscription);
    }

    /** Attach/replace proof while the payment is still pending. */
    public function update(User $user, Payment $payment): bool
    {
        return $user->isAdmin()
            || ($payment->status === PaymentStatus::Pending && $this->isBillingContact($user, $payment->subscription));
    }

    /** Mark as paid / failed / refunded. */
    public function confirm(User $user, Payment $payment): bool
    {
        return $user->isAdmin();
    }

    private function isBillingContact(User $user, Subscription $subscription): bool
    {
        return $this->hasOrgRole($user, $subscription->organization, OrgRole::Owner, OrgRole::Approver);
    }
}
