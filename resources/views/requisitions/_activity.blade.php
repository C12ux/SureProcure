{{-- Status timeline, newest first. Pass staff=true to use staff wording. --}}
<section class="app-card">
    <h3 class="app-card-title">Activity</h3>
    <ol class="mt-5 space-y-5">
        @foreach ($requisition->statusHistory->reverse() as $entry)
            <li>
                <x-status-badge :status="$entry->to_status" :staff="$staff ?? false" />
                <p class="mt-2 text-sm text-ink/70">
                    {{ $entry->created_at->diffForHumans() }}@if ($entry->changedBy), by {{ $entry->changedBy->name }}@endif
                </p>
                @if ($entry->note)
                    <p class="mt-1 text-sm">{{ $entry->note }}</p>
                @endif
            </li>
        @endforeach
    </ol>
</section>
