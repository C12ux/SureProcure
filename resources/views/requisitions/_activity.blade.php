{{-- Status timeline, newest first. Pass staff=true for staff wording. Entries where the status did not change are notes. --}}
<section class="app-card">
    <h3 class="app-card-title">Activity</h3>
    <ol class="mt-5 space-y-5">
        @foreach ($requisition->statusHistory->reverse() as $entry)
            <li>
                @if ($entry->from_status !== null && $entry->from_status === $entry->to_status)
                    <span class="badge badge--neutral"><span class="badge-dot"></span>Note</span>
                @else
                    <x-status-badge :status="$entry->to_status" :staff="$staff ?? false" />
                @endif
                <p class="mt-2 text-sm text-ink/70">
                    {{ $entry->created_at->diffForHumans() }}@if ($entry->changedBy), by {{ $entry->changedBy->name }}@endif
                </p>
                @if ($entry->note)
                    <p class="mt-1 whitespace-pre-line text-sm">{{ $entry->note }}</p>
                @endif
            </li>
        @endforeach
    </ol>
</section>
