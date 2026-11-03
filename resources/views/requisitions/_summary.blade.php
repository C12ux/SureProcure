{{-- Details + items cards, read-only. Needs $requisition with items, category, requester loaded. --}}
@php
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
    $estimatedTotal = $requisition->items->sum(fn ($item) => $item->line_total);
@endphp

<section class="app-card">
    <h3 class="app-card-title">Details</h3>
    <dl class="mt-5 grid gap-5 sm:grid-cols-2">
        <div><dt class="detail-label">Category</dt><dd class="detail-value">{{ $requisition->category?->name ?? 'Not set' }}</dd></div>
        <div><dt class="detail-label">Priority</dt><dd class="detail-value">{{ $requisition->priority->label() }}</dd></div>
        <div><dt class="detail-label">Needed by</dt><dd class="detail-value">{{ $requisition->needed_by_date?->format('F j, Y') ?? 'Not set' }}</dd></div>
        <div><dt class="detail-label">Budget</dt><dd class="detail-value tabular-nums">{{ $requisition->budget !== null ? $money($requisition->budget) : 'Not set' }}</dd></div>
        <div><dt class="detail-label">Requested by</dt><dd class="detail-value">{{ $requisition->requester?->name }}</dd></div>
        <div><dt class="detail-label">Submitted</dt><dd class="detail-value">{{ $requisition->submitted_at?->format('F j, Y') ?? 'Not yet' }}</dd></div>
    </dl>
    @if ($requisition->description)
        <p class="mt-6 whitespace-pre-line text-ink/80">{{ $requisition->description }}</p>
    @endif
</section>

<section class="app-card app-card--flush">
    <h3 class="app-card-title px-6 pb-4 pt-6">Items</h3>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Item</th><th>Quantity</th><th>Unit price</th><th>Line total</th></tr>
            </thead>
            <tbody>
                @foreach ($requisition->items as $item)
                    <tr>
                        <td class="min-w-[12rem]">
                            {{ $item->description }}
                            @if ($item->specifications)
                                <span class="block text-ink/60">{{ $item->specifications }}</span>
                            @endif
                        </td>
                        <td class="whitespace-nowrap tabular-nums">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                        <td class="whitespace-nowrap tabular-nums">{{ $item->estimated_unit_price !== null ? $money($item->estimated_unit_price) : 'Not set' }}</td>
                        <td class="whitespace-nowrap tabular-nums">{{ $item->estimated_unit_price !== null ? $money($item->line_total) : 'Not set' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <p class="border-t border-ink/10 px-6 py-4 text-right font-display text-lg font-bold tabular-nums">Estimated total {{ $money($estimatedTotal) }}</p>
</section>
