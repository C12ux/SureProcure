@php
    [$label, $tone] = $outcome;
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
@endphp

<x-app-layout>
    <x-slot name="title">{{ $requisition->pr_number }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('vendor.requests.index') }}" class="btn btn-sm btn-outline-dark">All requests</a>
    </x-slot>

    <div class="flex flex-wrap items-center gap-3">
        <h2 class="font-display text-2xl font-bold tracking-tight">{{ $requisition->title }}</h2>
        <span class="badge badge--{{ $tone }}"><span class="badge-dot"></span>{{ $label }}</span>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-3">

        <div class="space-y-6 lg:col-span-2">

            <section class="app-card">
                <h3 class="app-card-title">Your quotation</h3>
                @if ($quotation)
                    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                        <div><dt class="detail-label">Total amount</dt><dd class="detail-value tabular-nums">{{ $money($quotation->total_amount) }}</dd></div>
                        <div><dt class="detail-label">Delivery time</dt><dd class="detail-value">{{ $quotation->lead_time_days !== null ? $quotation->lead_time_days.' days' : 'Not given' }}</dd></div>
                        <div><dt class="detail-label">Valid until</dt><dd class="detail-value">{{ $quotation->valid_until?->format('F j, Y') ?? 'Not given' }}</dd></div>
                        <div><dt class="detail-label">Payment terms</dt><dd class="detail-value">{{ $quotation->payment_terms ?? 'Not given' }}</dd></div>
                    </dl>
                @else
                    <p class="mt-3 text-sm text-ink/70">No quotation has been recorded yet. Send your quotation to your procurement agent, who records it for you.</p>
                @endif
            </section>

            @foreach ($orders as $po)
                @php [$poLabel, $poTone] = \App\Support\VendorPortal::orderStatus($po->status); @endphp
                <section class="app-card">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h3 class="app-card-title">Purchase order</h3>
                        <span class="badge badge--{{ $poTone }}"><span class="badge-dot"></span>{{ $poLabel }}</span>
                    </div>
                    <dl class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div><dt class="detail-label">Number</dt><dd class="detail-value tabular-nums">{{ $po->po_number }}</dd></div>
                        <div><dt class="detail-label">Amount</dt><dd class="detail-value tabular-nums">{{ $po->currency }} {{ number_format((float) $po->amount, 2) }}</dd></div>
                        <div><dt class="detail-label">Issued</dt><dd class="detail-value">{{ $po->issued_at?->format('F j, Y') }}</dd></div>
                        <div class="sm:col-span-3"><dt class="detail-label">Payment terms</dt><dd class="detail-value">{{ $po->quotation?->payment_terms ?? 'As in your quotation' }}</dd></div>
                        <div class="sm:col-span-3"><dt class="detail-label">Delivery terms</dt><dd class="detail-value">{{ $po->quotation?->delivery_terms ?? 'As in your quotation' }}</dd></div>
                    </dl>
                    @if ($po->status === \App\Enums\PoStatus::Issued)
                        <form method="POST" action="{{ route('vendor.orders.acknowledge', $po) }}" class="mt-5">
                            @csrf
                            <button type="submit" class="btn btn-md btn-solid">Acknowledge order</button>
                        </form>
                    @endif
                </section>
            @endforeach

            <section class="app-card">
                <h3 class="app-card-title">What is needed</h3>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><dt class="detail-label">Category</dt><dd class="detail-value">{{ $requisition->category?->name ?? 'Not set' }}</dd></div>
                    <div><dt class="detail-label">Needed by</dt><dd class="detail-value">{{ $requisition->needed_by_date?->format('F j, Y') ?? 'Not set' }}</dd></div>
                </dl>
                @if ($requisition->description)
                    <p class="mt-5 whitespace-pre-line text-ink/80">{{ $requisition->description }}</p>
                @endif
            </section>

            <section class="app-card app-card--flush">
                <h3 class="app-card-title px-6 pb-4 pt-6">Items</h3>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead><tr><th>Item</th><th>Specifications</th><th>Quantity</th></tr></thead>
                        <tbody>
                            @foreach ($requisition->items as $item)
                                <tr>
                                    <td class="min-w-[12rem]">{{ $item->description }}</td>
                                    <td class="text-ink/70">{{ $item->specifications ?? 'None given' }}</td>
                                    <td class="whitespace-nowrap tabular-nums">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="app-card">
                <h3 class="app-card-title">Your procurement agent</h3>
                @if ($requisition->officer)
                    <p class="mt-4 font-semibold">{{ $requisition->officer->name }}</p>
                    <a href="mailto:{{ $requisition->officer->email }}" class="auth-link text-sm">{{ $requisition->officer->email }}</a>
                    @if ($requisition->officer->phone)
                        <p class="mt-1 text-sm text-ink/70">{{ $requisition->officer->phone }}</p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-ink/70">SureProcure will be in touch about this request.</p>
                @endif
            </section>

            @if ($customer)
                <section class="app-card">
                    <h3 class="app-card-title">Customer</h3>
                    <p class="mt-4 font-semibold">{{ $customer['company'] }}</p>
                    @if ($customer['name'])<p>{{ $customer['name'] }}</p>@endif
                    @if ($customer['email'])<a href="mailto:{{ $customer['email'] }}" class="auth-link text-sm">{{ $customer['email'] }}</a>@endif
                    @if ($customer['phone'])<p class="mt-1 text-sm text-ink/70">{{ $customer['phone'] }}</p>@endif
                </section>
            @elseif ($contactNote)
                <section class="app-card">
                    <h3 class="app-card-title">Customer</h3>
                    <p class="mt-3 text-sm text-ink/70">{{ $contactNote }}</p>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
