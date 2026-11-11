<x-app-layout>
    <x-slot name="title">Requests</x-slot>

    @php
        $tabs = ['action' => 'Needs my action', 'waiting' => 'Waiting on customer', 'completed' => 'Completed', 'all' => 'All'];
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap gap-1" aria-label="Filter by status">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('officer.requisitions.index', array_filter(['status' => $key === 'action' ? null : $key, 'q' => $search ?: null])) }}"
                   class="tab {{ $filter === $key ? 'tab--active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('officer.requisitions.index') }}" class="flex gap-2">
            @if ($filter !== 'action')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <label class="sr-only" for="q">Search requests</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Title, number or company" class="field-input w-full lg:w-72">
            <button type="submit" class="btn btn-md btn-outline-dark">Search</button>
        </form>
    </div>

    @if ($requisitions->isEmpty())
        <x-empty-state class="mt-6" title="No requests here"
            :text="($search !== '' || $filter !== 'action') ? 'Try a different filter or search.' : 'Requests assigned to you by an admin will appear here.'">
            @if ($search !== '' || $filter !== 'action')
                <a href="{{ route('officer.requisitions.index') }}" class="btn btn-md btn-outline-dark">Clear filters</a>
            @endif
        </x-empty-state>
    @else
        <div class="app-card app-card--flush mt-6">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead><tr><th>Number</th><th>Request</th><th>Needed by</th><th>Status</th><th>Updated</th></tr></thead>
                    <tbody>
                        @foreach ($requisitions as $pr)
                            <tr>
                                <td class="whitespace-nowrap font-semibold tabular-nums">
                                    <a href="{{ route('officer.requisitions.show', $pr) }}" class="auth-link">{{ $pr->pr_number }}</a>
                                </td>
                                <td class="min-w-[12rem]">
                                    {{ $pr->title }}
                                    <span class="block text-ink/60">{{ $pr->organization?->display_name }}</span>
                                </td>
                                <td class="whitespace-nowrap">{{ $pr->needed_by_date?->format('M j, Y') ?? 'Not set' }}</td>
                                <td><x-status-badge :status="$pr->status" :staff="true" /></td>
                                <td class="whitespace-nowrap text-ink/70">{{ $pr->updated_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $requisitions->links() }}</div>
    @endif
</x-app-layout>
