<x-app-layout>
    <x-slot name="title">Vendors</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.invitations.create') }}" class="btn btn-sm btn-solid inline-flex items-center gap-2">
            <x-icon name="plus" class="h-4 w-4" /> Invite a vendor
        </a>
    </x-slot>

    @php
        $tabs = [
            'all' => 'All',
            'review' => 'Needs review',
            'accredited' => 'Accredited',
            'in_progress' => 'Not submitted or sent back',
            'rejected' => 'Rejected',
        ];
    @endphp

    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <nav class="flex flex-wrap gap-1" aria-label="Filter by status">
            @foreach ($tabs as $key => $label)
                <a href="{{ route('admin.vendors.index', array_filter(['status' => $key === 'all' ? null : $key, 'q' => $search ?: null])) }}"
                   class="tab {{ $filter === $key ? 'tab--active' : '' }}">{{ $label }}</a>
            @endforeach
        </nav>

        <form method="GET" action="{{ route('admin.vendors.index') }}" class="flex gap-2">
            @if ($filter !== 'all')
                <input type="hidden" name="status" value="{{ $filter }}">
            @endif
            <label class="sr-only" for="q">Search vendors</label>
            <input id="q" type="search" name="q" value="{{ $search }}" placeholder="Search by company" class="field-input w-full lg:w-64">
            <button type="submit" class="btn btn-md btn-outline-dark">Search</button>
        </form>
    </div>

    @if ($vendors->isEmpty())
        <x-empty-state class="mt-6" title="No vendors here"
            :text="($search !== '' || $filter !== 'all') ? 'Try a different filter or search.' : 'Vendors appear here once they accept an invitation.'">
            <a href="{{ route('admin.invitations.create') }}" class="btn btn-md btn-solid">Invite a vendor</a>
        </x-empty-state>
    @else
        <div class="app-card app-card--flush mt-6">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Company</th><th>Contact</th><th>Status</th><th>Submitted</th><th>Accredited until</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($vendors as $vendor)
                            <tr>
                                <td class="min-w-[12rem] font-semibold">
                                    <a href="{{ route('admin.vendors.show', $vendor) }}" class="auth-link">{{ $vendor->name }}</a>
                                </td>
                                <td class="min-w-[12rem]">
                                    {{ $vendor->representative_name ?? 'Not given' }}
                                    <span class="block text-ink/60">{{ $vendor->organization?->email }}</span>
                                </td>
                                <td><x-vendor-badge :vendor="$vendor" /></td>
                                <td class="whitespace-nowrap text-ink/70">{{ $vendor->submitted_at?->format('M j, Y') ?? 'Not yet' }}</td>
                                <td class="whitespace-nowrap text-ink/70">{{ $vendor->accreditation_expires_at?->format('M j, Y') ?? 'Not set' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $vendors->links() }}</div>
    @endif
</x-app-layout>
