<?php

namespace App\Support;

use App\Enums\InvitationStatus;
use App\Enums\OrgRole;
use App\Enums\PoStatus;
use App\Enums\PrStatus;
use App\Enums\QuotationStatus;
use App\Models\PrVendorInvitation;
use App\Models\PurchaseRequisition;
use App\Models\Quotation;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

/**
 * Everything a vendor's dashboard shows, in one place, and what it deliberately leaves out.
 * Vendors never see the customer's budget or estimated prices, and see customer contact
 * details only when config('sureprocure.vendor_customer_contacts') allows it.
 */
class VendorPortal
{
    public function __construct(private Vendor $vendor) {}

    /** Invitations to requests the customer has submitted (customer drafts never reach a vendor). */
    public function invitations(): Builder
    {
        return PrVendorInvitation::query()
            ->where('vendor_id', $this->vendor->id)
            ->whereHas('purchaseRequisition', fn ($q) => $q->where('status', '!=', PrStatus::Draft->value));
    }

    /** Invitations with what the vendor needs to list them, newest first. */
    public function invitationsForList(): Builder
    {
        return $this->invitations()
            ->with(['purchaseRequisition.category', 'purchaseRequisition.officer'])
            ->orderByDesc('invited_at');
    }

    /** This vendor's own quotations, keyed by request id. */
    public function quotationsByRequest(): Collection
    {
        return $this->vendor->quotations()->get()->keyBy('purchase_requisition_id');
    }

    /** One list row: the request, this vendor's quotation on it, and how it is going. */
    public function row(PrVendorInvitation $invitation, Collection $quotations): array
    {
        $pr = $invitation->purchaseRequisition;
        $quotation = $quotations->get($invitation->purchase_requisition_id);

        return [
            'pr' => $pr,
            'invitation' => $invitation,
            'quotation' => $quotation,
            'outcome' => self::outcome($pr, $quotation, $invitation),
        ];
    }

    /** @return array{0: string, 1: string} label and badge tone */
    public static function outcome(PurchaseRequisition $pr, ?Quotation $quotation, PrVendorInvitation $invitation): array
    {
        if ($quotation) {
            return match (true) {
                $quotation->status === QuotationStatus::Awarded => ['Awarded', 'success'],
                $quotation->status === QuotationStatus::Rejected => ['Not selected', 'neutral'],
                in_array($pr->status, [PrStatus::Cancelled, PrStatus::Rejected], true) => ['Request closed', 'neutral'],
                $pr->status === PrStatus::Closed => ['Not selected', 'neutral'],
                $quotation->status === QuotationStatus::Shortlisted => ['Under review', 'info'],
                default => ['Quote submitted', 'info'],
            };
        }

        if (in_array($pr->status, [PrStatus::Cancelled, PrStatus::Rejected, PrStatus::Closed], true)) {
            return ['Request closed', 'neutral'];
        }

        if ($invitation->status === InvitationStatus::Declined) {
            return ['Declined', 'neutral'];
        }

        if (! in_array($pr->status, [PrStatus::Sourcing, PrStatus::NeedsClarification], true)) {
            return ['Closed to new quotes', 'neutral'];
        }

        return ['Awaiting your quote', 'attention'];
    }

    /** Numbers for the dashboard tiles ("visibility to data"). */
    public function stats(): array
    {
        $awarded = $this->vendor->quotations()->where('status', QuotationStatus::Awarded->value);

        return [
            'invited' => $this->invitations()->count(),
            'awaiting' => $this->invitations()
                ->where('status', InvitationStatus::Invited->value)
                ->whereHas('purchaseRequisition', fn ($q) => $q->where('status', PrStatus::Sourcing->value))
                ->count(),
            'quoted' => $this->vendor->quotations()->count(),
            'awarded' => (clone $awarded)->count(),
            'awarded_value' => (float) (clone $awarded)->sum('total_amount'),
        ];
    }

    /** The SureProcure officers this vendor works with ("contact details of Procurement Agent"). */
    public function agents(): Collection
    {
        return $this->invitations()->with('purchaseRequisition.officer')->get()
            ->filter(fn ($invitation) => $invitation->purchaseRequisition?->officer)
            ->groupBy(fn ($invitation) => $invitation->purchaseRequisition->assigned_officer_id)
            ->map(fn ($group) => [
                'officer' => $group->first()->purchaseRequisition->officer,
                'requests' => $group->map(fn ($i) => $i->purchaseRequisition->pr_number)->values()->all(),
            ])
            ->values()
            ->take(5);
    }

    /** Purchase orders issued to this vendor, with the customer's contact when it may be shown. */
    public function orders(): Collection
    {
        return $this->vendor->purchaseOrders()
            ->with(['purchaseRequisition.organization.users', 'quotation'])
            ->latest('id')
            ->limit(5)
            ->get()
            ->map(fn ($po) => [
                'po' => $po,
                'pr' => $po->purchaseRequisition,
                'customer' => $po->purchaseRequisition ? $this->customerContact($po->purchaseRequisition, $po->quotation) : null,
            ]);
    }

    /** @return array{0: string, 1: string} label and badge tone for a purchase order, in the vendor's words */
    public static function orderStatus(PoStatus $status): array
    {
        return match ($status) {
            PoStatus::Issued => ['New order', 'attention'],
            PoStatus::Acknowledged => ['Acknowledged', 'info'],
            PoStatus::Completed => ['Completed', 'success'],
            PoStatus::Cancelled => ['Cancelled', 'neutral'],
        };
    }

    /**
     * Performance numbers for the vendor's charts. Everything here is the vendor's own data:
     * nothing about other vendors, evaluation scores or the customer's budget.
     */
    public function metrics(): array
    {
        $quotations = $this->vendor->quotations()->with('purchaseRequisition')->get();
        $invitations = $this->invitations()->with('purchaseRequisition.category')->get();
        $orders = $this->vendor->purchaseOrders()->get();

        // How the quotations turned out
        $finished = [PrStatus::Closed, PrStatus::Rejected, PrStatus::Cancelled];
        $outcomes = ['awarded' => 0, 'evaluating' => 0, 'not_selected' => 0];

        foreach ($quotations as $quotation) {
            if ($quotation->status === QuotationStatus::Awarded) {
                $outcomes['awarded']++;
            } elseif ($quotation->status === QuotationStatus::Rejected || in_array($quotation->purchaseRequisition?->status, $finished, true)) {
                $outcomes['not_selected']++;
            } else {
                $outcomes['evaluating']++;
            }
        }

        $decided = $outcomes['awarded'] + $outcomes['not_selected'];

        // How quickly the vendor answers an invitation
        $invitedAt = $invitations->keyBy('purchase_requisition_id');
        $hours = $quotations
            ->map(function ($quotation) use ($invitedAt) {
                $invitation = $invitedAt->get($quotation->purchase_requisition_id);

                return $invitation?->invited_at ? abs($invitation->invited_at->diffInMinutes($quotation->created_at)) / 60 : null;
            })
            ->filter(fn ($h) => $h !== null);

        // The last six months of quotation value, and the part of it that was won
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $start = now()->startOfMonth()->subMonths($i);
            $inMonth = $quotations->filter(fn ($q) => $q->created_at->format('Y-m') === $start->format('Y-m'));

            $months[] = [
                'label' => $start->format('M'),
                'quoted' => (float) $inMonth->sum('total_amount'),
                'awarded' => (float) $inMonth->filter(fn ($q) => $q->status === QuotationStatus::Awarded)->sum('total_amount'),
            ];
        }

        // Which categories the invitations come from
        $quotedIds = $quotations->pluck('purchase_requisition_id')->all();
        $categories = $invitations
            ->groupBy(fn ($invitation) => $invitation->purchaseRequisition?->category?->name ?? 'No category')
            ->map(fn ($group, $name) => [
                'name' => $name,
                'invited' => $group->count(),
                'quoted' => $group->filter(fn ($i) => in_array($i->purchase_requisition_id, $quotedIds, true))->count(),
            ])
            ->sortByDesc('invited')
            ->take(5)
            ->values()
            ->all();

        // Open orders and completed orders, like a procurement team tracks them
        $open = $orders->filter(fn ($o) => in_array($o->status, [PoStatus::Issued, PoStatus::Acknowledged], true));
        $completed = $orders->filter(fn ($o) => $o->status === PoStatus::Completed);
        $withLeadTime = $quotations->whereNotNull('lead_time_days');

        return [
            'has_data' => $quotations->isNotEmpty() || $invitations->isNotEmpty(),
            'outcomes' => $outcomes,
            'decided' => $decided,
            'win_rate' => $decided > 0 ? (int) round($outcomes['awarded'] / $decided * 100) : null,
            'response_hours' => $hours->isNotEmpty() ? (float) $hours->avg() : null,
            'avg_lead_days' => $withLeadTime->isNotEmpty() ? (float) $withLeadTime->avg('lead_time_days') : null,
            'avg_quote' => $quotations->isNotEmpty() ? (float) $quotations->avg('total_amount') : null,
            'months' => $months,
            'categories' => $categories,
            'orders' => [
                'new' => $orders->filter(fn ($o) => $o->status === PoStatus::Issued)->count(),
                'open' => $open->count(),
                'open_value' => (float) $open->sum('amount'),
                'completed' => $completed->count(),
                'completed_value' => (float) $completed->sum('amount'),
            ],
        ];
    }

    /** The customer's contact, or null when the rules do not allow this vendor to see it yet. */
    public function customerContact(PurchaseRequisition $pr, ?Quotation $quotation): ?array
    {
        $allowed = match (config('sureprocure.vendor_customer_contacts', 'awarded')) {
            'invited' => true,
            'awarded' => $quotation?->status === QuotationStatus::Awarded,
            default => false,
        };

        if (! $allowed) {
            return null;
        }

        $pr->loadMissing('organization.users');
        $organization = $pr->organization;

        if (! $organization) {
            return null;
        }

        $owner = $organization->users->first(fn ($u) => $u->pivot->org_role === OrgRole::Owner) ?? $organization->users->first();

        return [
            'company' => $organization->display_name,
            'name' => $owner?->name,
            'email' => $owner?->email,
            'phone' => $owner?->phone,
        ];
    }

    /** What to tell a vendor when a customer's contact is not shown yet. */
    public static function contactNote(): ?string
    {
        return config('sureprocure.vendor_customer_contacts', 'awarded') === 'awarded'
            ? 'The customer’s contact details are shared once you are awarded the order.'
            : null;
    }
}
