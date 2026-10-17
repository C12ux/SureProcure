<?php

namespace App\Enums;

use App\Enums\Concerns\HasOptions;

enum PrStatus: string
{
    use HasOptions;

    case Draft = 'draft';
    case Submitted = 'submitted';
    case InReview = 'in_review';
    case NeedsClarification = 'needs_clarification';
    case Sourcing = 'sourcing';
    case BidEvaluation = 'bid_evaluation';
    case AwaitingClientReview = 'awaiting_client_review';
    case Approved = 'approved';
    case PoIssued = 'po_issued';
    case Closed = 'closed';
    case Rejected = 'rejected';
    case Cancelled = 'cancelled';

    /**
     * The PR workflow. Adjust here if the business flow changes;
     * PurchaseRequisition::transitionTo() enforces it.
     *
     * @return array<int, self>
     */
    public function allowedTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Submitted, self::Cancelled],
            self::Submitted => [self::InReview, self::NeedsClarification, self::Rejected, self::Cancelled],
            self::InReview => [self::Sourcing, self::NeedsClarification, self::Rejected, self::Cancelled],
            self::NeedsClarification => [self::InReview, self::Sourcing, self::BidEvaluation, self::Cancelled],
            self::Sourcing => [self::BidEvaluation, self::NeedsClarification, self::Cancelled],
            self::BidEvaluation => [self::AwaitingClientReview, self::Sourcing, self::Cancelled],
            self::AwaitingClientReview => [self::Approved, self::NeedsClarification, self::BidEvaluation, self::Rejected],
            self::Approved => [self::PoIssued, self::Cancelled],
            self::PoIssued => [self::Closed],
            self::Closed, self::Rejected, self::Cancelled => [],
        };
    }

    public function canTransitionTo(self $to): bool
    {
        return in_array($to, $this->allowedTransitions(), true);
    }

    public function isTerminal(): bool
    {
        return $this->allowedTransitions() === [];
    }
}
