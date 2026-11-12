<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\PrStatus;
use App\Http\Controllers\Controller;
use App\Models\PrVendorInvitation;
use App\Models\PurchaseRequisition;
use App\Models\Vendor;
use App\Support\VendorPortal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Read-only view of the requests a vendor was invited to quote on. The officer records quotations,
 * so there is nothing to edit here. The customer's budget and estimated prices are never shown.
 */
class RequestController extends Controller
{
    public function index(Request $request): View
    {
        $portal = new VendorPortal($this->vendor($request));
        $quotations = $portal->quotationsByRequest();

        $rows = $portal->invitationsForList()
            ->paginate(12)
            ->through(fn (PrVendorInvitation $invitation) => $portal->row($invitation, $quotations));

        return view('vendor.requests.index', compact('rows'));
    }

    public function show(Request $request, PurchaseRequisition $requisition): View
    {
        $vendor = $this->vendor($request);

        // Only requests this vendor was invited to (the policy checks the same thing), and never drafts.
        Gate::authorize('view', $requisition);
        abort_if($requisition->status === PrStatus::Draft, 404);

        $invitation = PrVendorInvitation::where('vendor_id', $vendor->id)
            ->where('purchase_requisition_id', $requisition->id)
            ->firstOrFail();

        $requisition->load(['category', 'officer', 'items']);

        $quotation = $vendor->quotations()->where('purchase_requisition_id', $requisition->id)->first();
        $portal = new VendorPortal($vendor);

        return view('vendor.requests.show', [
            'requisition' => $requisition,
            'quotation' => $quotation,
            'orders' => $requisition->purchaseOrders()->where('vendor_id', $vendor->id)->get(),
            'outcome' => VendorPortal::outcome($requisition, $quotation, $invitation),
            'customer' => $portal->customerContact($requisition, $quotation),
            'contactNote' => VendorPortal::contactNote(),
        ]);
    }

    private function vendor(Request $request): Vendor
    {
        return $request->user()->organizations->first()?->vendor
            ?? abort(403, 'Your account is not linked to a vendor company.');
    }
}
