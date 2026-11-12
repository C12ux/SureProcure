<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\OrgRole;
use App\Enums\PoStatus;
use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * "Receive and review the PO": the vendor confirms they have the order.
 * The officer closing the request later marks the order completed.
 */
class OrderController extends Controller
{
    public function acknowledge(Request $request, PurchaseOrder $order): RedirectResponse
    {
        $organization = $request->user()->organizations->first();
        $vendor = $organization?->vendor ?? abort(403);

        // Their own order only, and only an owner or approver of the vendor company may confirm it.
        abort_unless((int) $order->vendor_id === (int) $vendor->id, 403);
        abort_unless(in_array($request->user()->orgRole($organization), [OrgRole::Owner, OrgRole::Approver], true), 403);

        if ($order->status !== PoStatus::Issued) {
            return back()->with('warning', 'This order has already been acknowledged.');
        }

        DB::transaction(function () use ($order, $vendor, $request) {
            $order->forceFill(['status' => PoStatus::Acknowledged])->save();

            $pr = $order->purchaseRequisition;
            $pr?->statusHistory()->create([
                'from_status' => $pr->status,
                'to_status' => $pr->status,
                'changed_by' => $request->user()->id,
                'note' => "Purchase order {$order->po_number} acknowledged by {$vendor->name}",
            ]);
            $pr?->touch();
        });

        return back()->with('success', "Order {$order->po_number} acknowledged. Your procurement agent can see it on the request.");
    }
}
