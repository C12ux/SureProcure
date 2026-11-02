<x-app-layout>
    <x-slot name="title">Requisitions</x-slot>
    <x-slot name="actions">
        <a href="{{ route('requisitions.export') }}" class="btn btn-sm btn-outline-dark hidden items-center gap-2 sm:inline-flex">
            <x-icon name="download" class="h-4 w-4" /> Download data
        </a>
        <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-solid inline-flex items-center gap-2">
            <x-icon name="plus" class="h-4 w-4" /> New requisition
        </a>
    </x-slot>

    @php
        $tabs = [
            'all' => 'All',
            'drafts' => 'Drafts',
            'in_progress' => 'In progress',
            'attention' => 'Needs your attention',
            'completed' => 'Completed',
            'closed_out' => 'Rejected or cancelled',
        ];
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap gap-1" aria-label="Filter by status">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('requisitions.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   class="tab {{ $filter === $key ? 'tab--active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('requisitions.index') }}" class="flex gap-2">
            @if ($filter !== 'all')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <label class="sr-only" for="q">Search requisitions</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Search by title or number" class="field-input w-full lg:w-72">
            <button type="submit" class="btn btn-md btn-outline-dark">Search</button>
        </form>
    </div>

    @if ($requisitions->isEmpty())
        <x-empty-state class="mt-6"
            :title="($search !== '' || $filter !== 'all') ? 'No requisitions match' : 'No requisitions yet'"
            :text="($search !== '' || $filter !== 'all') ? 'Try a different filter or search.' : 'Describe what you need and save it as a draft.'">
            @if ($search === '' && $filter === 'all')
                <a href="{{ route('requisitions.create') }}" class="btn btn-md btn-solid">Create a requisition</a>
            @else
                <a href="{{ route('requisitions.index') }}" class="btn btn-md btn-outline-dark">Clear filters</a>
            @endif
        </x-empty-state>
    @else
        <div class="app-card app-card--flush mt-6">
            @include('requisitions._table', ['rows' => $requisitions])
        </div>
        <div class="mt-6">{{ $requisitions->links() }}</div>
    @endif
</x-app-layout>
