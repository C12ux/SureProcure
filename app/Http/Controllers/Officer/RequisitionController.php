<?php

namespace App\Http\Controllers\Officer;

use App\Enums\BidEvaluationStatus;
use App\Enums\InvitationStatus;
use App\Enums\PrStatus;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Models\PrVendorInvitation;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * The procurement officer's view of their assigned requests.
 * Everything is scoped with PurchaseRequisition::visibleTo(), which for officers
 * means "assigned to me". Drafts are private to the customer and never shown.
 */
class RequisitionController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('status', 'action');
        $search = trim((string) $request->query('q', ''));

        $query = PurchaseRequisition::visibleTo($request->user())
            ->where('status', '!=', PrStatus::Draft->value)
            ->with('organization')
            ->latest('updated_at');

        if ($filter === 'waiting') {
            $query->withStatus(...PrStatus::waitingOnCustomer());
        } elseif ($filter === 'completed') {
            $query->withStatus(PrStatus::Closed);
        } elseif ($filter === 'all') {
            // no extra filter
        } else {
            $filter = 'action';
            $query->withStatus(...PrStatus::officerAction());
        }

        if ($search !== '') {
            $term = '%'.addcslashes($search, '%_\\').'%';
            $query->where(function (Builder $q) use ($term) {
                $q->where('title', 'ilike', $term)
                    ->orWhere('pr_number', 'ilike', $term)
                    ->orWhereHas('organization', fn (Builder $o) => $o->where('legal_name', 'ilike', $term));
            });
        }

        return view('officer.requisitions.index', [
            'requisitions' => $query->paginate(12)->withQueryString(),
            'filter' => $filter,
            'search' => $search,
        ]);
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        Gate::authorize('view', $requisition);
        abort_if($requisition->status === PrStatus::Draft, 404);

        $requisition->load([
            'organization.users', 'requester', 'category', 'items', 'statusHistory.changedBy',
            'invitations.vendor.organization',
            'quotations.vendor.organization', 'quotations.documents',
            'bidEvaluation.recommendedQuotation.vendor.organization',
            'purchaseOrders.vendor.organization',
        ]);

        $canSource = $requisition->status === PrStatus::Sourcing && Gate::allows('inviteVendor', $requisition);

        // Accredited vendors not yet invited, those matching the request's category first.
        $candidates = collect();
        if ($canSource) {
            $candidates = Vendor::accredited()
                ->whereNotIn('id', $requisition->invitations->pluck('vendor_id'))
                ->with(['organization', 'categories'])
                ->get()
                ->sortBy(fn (Vendor $v) => [
                    $requisition->category_id && $v->categories->contains('id', $requisition->category_id) ? 0 : 1,
                    mb_strtolower($v->name),
                ])
                ->values();
        }

        return view('officer.requisitions.show', compact('requisition', 'canSource', 'candidates'));
    }

    /** The steps an officer takes by hand. Sending the evaluation and issuing the PO have their own actions. */
    public function transition(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        $data = $request->validate([
            'to' => ['required', Rule::in([
                PrStatus::InReview->value, PrStatus::NeedsClarification->value, PrStatus::Sourcing->value,
                PrStatus::BidEvaluation->value, PrStatus::Closed->value,
            ])],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $to = PrStatus::from($data['to']);
        Gate::authorize('transition', [$requisition, $to]);

        if ($to === PrStatus::NeedsClarification && blank($data['note'] ?? null)) {
            throw ValidationException::withMessages(['note' => 'Say what you need from the customer.']);
        }

        if ($to === PrStatus::BidEvaluation && $requisition->quotations()->doesntExist()) {
            throw ValidationException::withMessages(['to' => 'Record at least one quotation before evaluating bids.']);
        }

        $requisition->transitionTo($to, $request->user(), $data['note'] ?? null);

        // Pulling a request back to revise it: the evaluation is no longer with the customer.
        if (in_array($to, [PrStatus::Sourcing, PrStatus::BidEvaluation], true)) {
            $evaluation = $requisition->bidEvaluation;

            if ($evaluation && $evaluation->status === BidEvaluationStatus::Submitted) {
                $evaluation->forceFill(['status' => BidEvaluationStatus::Draft, 'submitted_at' => null])->save();
                $requisition->quotations()
                    ->where('status', QuotationStatus::Shortlisted->value)
                    ->update(['status' => QuotationStatus::Received->value]);
            }
        }

        return redirect()->route('officer.requisitions.show', $requisition)->with('success', "Moved to {$to->staffLabel()}.");
    }

    /** A plain note in the activity feed, visible to the customer. */
    public function reply(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('view', $requisition);

        if ($requisition->status->isTerminal()) {
            return back()->with('warning', 'This request is finished, so notes can no longer be added.');
        }

        $data = $request->validate(['message' => ['required', 'string', 'max:2000']]);

        $requisition->statusHistory()->create([
            'from_status' => $requisition->status,
            'to_status' => $requisition->status,
            'changed_by' => $request->user()->id,
            'note' => $data['message'],
        ]);
        $requisition->touch();

        return back()->with('success', 'Note added.');
    }

    public function invite(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('inviteVendor', $requisition);

        $data = $request->validate([
            'vendor_ids' => ['required', 'array', 'min:1'],
            'vendor_ids.*' => ['integer'],
        ], ['vendor_ids.required' => 'Choose at least one vendor to invite.', 'vendor_ids.min' => 'Choose at least one vendor to invite.']);

        // Only accredited vendors can be invited, whatever the form says.
        $vendors = Vendor::accredited()->with('organization')->whereIn('id', $data['vendor_ids'])->get();

        if ($vendors->isEmpty()) {
            throw ValidationException::withMessages(['vendor_ids' => 'Only accredited vendors can be invited.']);
        }

        DB::transaction(function () use ($vendors, $requisition, $request) {
            foreach ($vendors as $vendor) {
                PrVendorInvitation::firstOrCreate(
                    ['purchase_requisition_id' => $requisition->id, 'vendor_id' => $vendor->id],
                    ['invited_by' => $request->user()->id, 'status' => InvitationStatus::Invited->value]
                );
            }

            $requisition->statusHistory()->create([
                'from_status' => $requisition->status,
                'to_status' => $requisition->status,
                'changed_by' => $request->user()->id,
                'note' => 'Invited to quote: '.$vendors->pluck('name')->join(', '),
            ]);
        });

        return back()->with('success', $vendors->count() === 1 ? 'Vendor invited.' : $vendors->count().' vendors invited.');
    }

    public function uninvite(PurchaseRequisition $requisition, PrVendorInvitation $invitation): RedirectResponse
    {
        Gate::authorize('inviteVendor', $requisition);
        abort_unless($invitation->purchase_requisition_id === $requisition->id, 404);

        if ($requisition->quotations()->where('vendor_id', $invitation->vendor_id)->exists()) {
            return back()->with('warning', 'This vendor already has a quotation. Remove the quotation first.');
        }

        $invitation->delete();

        return back()->with('success', 'Invitation removed.');
    }

    /** After the customer approves: raise the purchase order for the awarded quotation. */
    public function issuePo(Request $request, PurchaseRequisition $requisition): RedirectResponse
    {
        Gate::authorize('create', [PurchaseOrder::class, $requisition]);

        $quotation = $requisition->bidEvaluation?->recommendedQuotation;

        if (! $quotation || $quotation->status !== QuotationStatus::Awarded) {
            return back()->with('warning', 'There is no awarded quotation to raise a purchase order from.');
        }

        $po = DB::transaction(function () use ($requisition, $quotation, $request) {
            $po = PurchaseOrder::create([
                'purchase_requisition_id' => $requisition->id,
                'quotation_id' => $quotation->id,
                'vendor_id' => $quotation->vendor_id,
                'amount' => $quotation->total_amount,
                'currency' => $quotation->currency,
                'issued_at' => now(),
            ]);

            $requisition->transitionTo(PrStatus::PoIssued, $request->user(), "Purchase order {$po->po_number} issued to {$quotation->vendor->name}");

            return $po;
        });

        return redirect()->route('officer.requisitions.show', $requisition)->with('success', "Purchase order {$po->po_number} issued.");
    }
}
