{{-- Purchase orders raised for a request. Needs $requisition with purchaseOrders.vendor.organization. --}}
@foreach ($requisition->purchaseOrders as $po)
    <section class="app-card">
        <h3 class="app-card-title">Purchase order</h3>
        <dl class="mt-4 grid gap-4 sm:grid-cols-2">
            <div><dt class="detail-label">Number</dt><dd class="detail-value tabular-nums">{{ $po->po_number }}</dd></div>
            <div><dt class="detail-label">Vendor</dt><dd class="detail-value">{{ $po->vendor?->name }}</dd></div>
            <div><dt class="detail-label">Amount</dt><dd class="detail-value tabular-nums">{{ $po->currency }} {{ number_format((float) $po->amount, 2) }}</dd></div>
            <div><dt class="detail-label">Issued</dt><dd class="detail-value">{{ $po->issued_at?->format('F j, Y') }}</dd></div>
        </dl>
    </section>
@endforeach
