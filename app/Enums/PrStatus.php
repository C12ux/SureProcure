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

    /** Friendly names shown to people. */
    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Submitted',
            self::InReview => 'In review',
            self::NeedsClarification => 'Needs your input',
            self::Sourcing => 'Sourcing vendors',
            self::BidEvaluation => 'Evaluating bids',
            self::AwaitingClientReview => 'Awaiting your approval',
            self::Approved => 'Approved',
            self::PoIssued => 'PO issued',
            self::Closed => 'Closed',
            self::Rejected => 'Rejected',
            self::Cancelled => 'Cancelled',
        };
    }

    /** Badge color family: neutral, info, attention, success, danger. */
    public function tone(): string
    {
        return match ($this) {
            self::Draft, self::Cancelled => 'neutral',
            self::Submitted, self::InReview, self::Sourcing, self::BidEvaluation => 'info',
            self::NeedsClarification, self::AwaitingClientReview => 'attention',
            self::Approved, self::PoIssued, self::Closed => 'success',
            self::Rejected => 'danger',
        };
    }

    /**
     * Position on the 7-step progress tracker
     * (Submitted, In review, Sourcing, Bid evaluation, Your approval, PO issued, Closed).
     * Steps before this index are done, this one is current. null = not on the tracker.
     */
    public function progressIndex(): ?int
    {
        return match ($this) {
            self::Draft => -1,
            self::Submitted => 0,
            self::InReview, self::NeedsClarification => 1,
            self::Sourcing => 2,
            self::BidEvaluation => 3,
            self::AwaitingClientReview => 4,
            self::Approved => 5,
            self::PoIssued => 6,
            self::Closed => 7,
            self::Rejected, self::Cancelled => null,
        };
    }

    /** Filter groups used by the requisition list and dashboard counts. */
    public static function groups(): array
    {
        return [
            'drafts' => [self::Draft],
            'in_progress' => [self::Submitted, self::InReview, self::Sourcing, self::BidEvaluation],
            'attention' => [self::NeedsClarification, self::AwaitingClientReview],
            'completed' => [self::Approved, self::PoIssued, self::Closed],
            'closed_out' => [self::Rejected, self::Cancelled],
        ];
    }

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
