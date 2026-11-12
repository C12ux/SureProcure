{{-- Purchase orders raised for a request. Needs $requisition with purchaseOrders.vendor.organization. --}}
@php
    $poText = ['issued' => 'Issued', 'acknowledged' => 'Acknowledged by the vendor', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
@endphp

@foreach ($requisition->purchaseOrders as $po)
    <section class="app-card">
        <h3 class="app-card-title">Purchase order</h3>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div><dt class="detail-label">Number</dt><dd class="detail-value tabular-nums">{{ $po->po_number }}</dd></div>
            <div><dt class="detail-label">Vendor</dt><dd class="detail-value">{{ $po->vendor?->name }}</dd></div>
            <div><dt class="detail-label">Amount</dt><dd class="detail-value tabular-nums">{{ $po->currency }} {{ number_format((float) $po->amount, 2) }}</dd></div>
            <div><dt class="detail-label">Issued</dt><dd class="detail-value">{{ $po->issued_at?->format('F j, Y') }}</dd></div>
            <div class="sm:col-span-2"><dt class="detail-label">Order status</dt><dd class="detail-value">{{ $poText[$po->status->value] ?? ucfirst($po->status->value) }}</dd></div>
        </dl>
    </section>
@endforeach
