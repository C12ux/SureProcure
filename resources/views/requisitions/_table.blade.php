<div class="overflow-x-auto">
    <table class="data-table">
        <thead>
            <tr>
                <th>Number</th>
                <th>Request</th>
                <th>Category</th>
                <th>Budget</th>
                <th>Needed by</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($rows as $pr)
                <tr>
                    <td class="whitespace-nowrap font-semibold tabular-nums">
                        <a href="{{ route('requisitions.show', $pr) }}" class="auth-link">{{ $pr->pr_number }}</a>
                    </td>
                    <td class="min-w-[12rem]">{{ $pr->title }}</td>
                    <td class="text-ink/70">{{ $pr->category?->name ?? 'Not set' }}</td>
                    <td class="whitespace-nowrap tabular-nums">{{ $pr->budget !== null ? $pr->currency.' '.number_format((float) $pr->budget, 2) : 'Not set' }}</td>
                    <td class="whitespace-nowrap">{{ $pr->needed_by_date?->format('M j, Y') ?? 'Not set' }}</td>
                    <td><x-status-badge :status="$pr->status" /></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
