<x-app-layout>
    <x-slot name="section">Requisitions</x-slot>
    <x-slot name="description">Monitor procurement requests and find items waiting for assignment.</x-slot>
    <x-slot name="title">All requisitions</x-slot>

    @php
        $tabs = [
            'all' => 'All',
            'unassigned' => 'Needs an officer',
            'in_progress' => 'In progress',
            'attention' => 'Waiting on customer',
            'completed' => 'Completed',
            'closed_out' => 'Rejected or cancelled',
        ];
    @endphp

    <div class="list-toolbar">
        <nav class="flex flex-wrap gap-1" aria-label="Filter by status">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.requisitions.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   class="tab {{ $filter === $key ? 'tab--active' : '' }}" @if ($filter === $key) aria-current="page" @endif>{{ $label }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.requisitions.index') }}" class="flex gap-2">
            @if ($filter !== 'all')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <label class="sr-only" for="q">Search requests</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Title, number or company" class="field-input w-full lg:w-72">
            <button type="submit" class="btn btn-md btn-outline-dark">Search</button>
        </form>
    </div>

    @if ($requisitions->isEmpty())
        <x-empty-state class="mt-6" title="No requests here"
            :text="($search !== '' || $filter !== 'all') ? 'Try a different filter or search.' : 'Customer requests appear here once they are submitted.'">
            @if ($search !== '' || $filter !== 'all')
                <a href="{{ route('admin.requisitions.index') }}" class="btn btn-md btn-outline-dark">Clear filters</a>
            @endif
        </x-empty-state>
    @else
        <div class="app-card app-card--flush mt-6">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Number</th><th>Request</th><th>Officer</th><th>Status</th><th>Updated</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($requisitions as $pr)
                            <tr>
                                <td class="whitespace-nowrap font-semibold tabular-nums">
                                    <a href="{{ route('admin.requisitions.show', $pr) }}" class="auth-link">{{ $pr->pr_number }}</a>
                                </td>
                                <td class="min-w-[12rem]">
                                    {{ $pr->title }}
                                    <span class="block text-ink/60">{{ $pr->organization?->display_name }}</span>
                                </td>
                                <td class="whitespace-nowrap">
                                    @if ($pr->officer)
                                        {{ $pr->officer->name }}
                                    @else
                                        <span class="badge badge--attention"><span class="badge-dot"></span>Unassigned</span>
                                    @endif
                                </td>
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
