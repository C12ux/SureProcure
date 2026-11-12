{{-- Requests a vendor was invited to. $rows: list of ['pr', 'invitation', 'quotation', 'outcome']. --}}
<div class="overflow-x-auto">
    <table class="data-table">
        <thead>
            <tr><th>Number</th><th>Request</th><th>Needed by</th><th>Your quotation</th><th>Procurement agent</th><th>Status</th></tr>
        </thead>
        <tbody>
            @foreach ($rows as $row)
                @php [$label, $tone] = $row['outcome']; @endphp
                <tr>
                    <td class="whitespace-nowrap font-semibold tabular-nums">
                        <a href="{{ route('vendor.requests.show', $row['pr']) }}" class="auth-link">{{ $row['pr']->pr_number }}</a>
                    </td>
                    <td class="min-w-[12rem]">
                        {{ $row['pr']->title }}
                        <span class="block text-ink/60">{{ $row['pr']->category?->name ?? 'No category' }}</span>
                    </td>
                    <td class="whitespace-nowrap">{{ $row['pr']->needed_by_date?->format('M j, Y') ?? 'Not set' }}</td>
                    <td class="whitespace-nowrap tabular-nums">
                        {{ $row['quotation'] ? $row['pr']->currency.' '.number_format((float) $row['quotation']->total_amount, 2) : 'None yet' }}
                    </td>
                    <td class="whitespace-nowrap">{{ $row['pr']->officer?->name ?? 'To be assigned' }}</td>
                    <td><span class="badge badge--{{ $tone }}"><span class="badge-dot"></span>{{ $label }}</span></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
