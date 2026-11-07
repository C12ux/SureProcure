<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.invitations.create') }}" class="btn btn-sm btn-solid inline-flex items-center gap-2">
            <x-icon name="plus" class="h-4 w-4" /> Invite someone
        </a>
    </x-slot>

    <p class="lead text-ink/70">Welcome back, {{ strtok($user->name, ' ') }}. Here is what needs you today.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        <a href="{{ route('admin.requisitions.index', ['status' => 'unassigned']) }}" class="block rounded-2xl">
            <x-stat label="Requests needing an officer" :value="$stats['unassigned']" :tone="$stats['unassigned'] > 0 ? 'attention' : 'neutral'" />
        </a>
        <a href="{{ route('admin.vendors.index', ['status' => 'review']) }}" class="block rounded-2xl">
            <x-stat label="Vendors awaiting review" :value="$stats['vendors_review']" :tone="$stats['vendors_review'] > 0 ? 'attention' : 'neutral'" />
        </a>
        <a href="{{ route('admin.requisitions.index') }}" class="block rounded-2xl">
            <x-stat label="Active requests" :value="$stats['active']" />
        </a>
        <a href="{{ route('admin.customers.index') }}" class="block rounded-2xl">
            <x-stat label="Customers" :value="$stats['customers']" />
        </a>
        <div><x-stat label="Procurement officers" :value="$stats['officers']" /></div>
        <a href="{{ route('admin.invitations.index') }}" class="block rounded-2xl">
            <x-stat label="Pending invitations" :value="$stats['invites']" />
        </a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-5">

        <section class="app-card app-card--flush lg:col-span-3">
            <div class="flex items-center justify-between px-6 pb-4 pt-6">
                <h2 class="app-card-title">Waiting for an officer</h2>
                <a href="{{ route('admin.requisitions.index', ['status' => 'unassigned']) }}" class="auth-link text-sm">View all</a>
            </div>
            @forelse ($needsOfficer as $pr)
                <a href="{{ route('admin.requisitions.show', $pr) }}" class="flex items-center justify-between gap-4 border-t border-ink/10 px-6 py-4 hover:bg-mist-50">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold tabular-nums">{{ $pr->pr_number }}</p>
                        <p class="truncate">{{ $pr->title }}</p>
                        <p class="text-sm text-ink/60">{{ $pr->organization?->display_name }}</p>
                    </div>
                    <x-status-badge :status="$pr->status" :staff="true" />
                </a>
            @empty
                <p class="border-t border-ink/10 px-6 py-6 text-sm text-ink/70">Every active request has an officer.</p>
            @endforelse
        </section>

        <section class="app-card lg:col-span-2">
            <h2 class="app-card-title">Recent activity</h2>
            <ol class="mt-5 space-y-5">
                @forelse ($activity as $entry)
                    <li>
                        <a href="{{ route('admin.requisitions.show', $entry->purchaseRequisition) }}" class="block hover:opacity-80">
                            <p class="text-sm font-semibold tabular-nums">{{ $entry->purchaseRequisition->pr_number }}</p>
                            <p class="text-sm text-ink/60">{{ $entry->purchaseRequisition->organization?->display_name }}</p>
                            <x-status-badge :status="$entry->to_status" :staff="true" class="mt-2" />
                            <p class="mt-1 text-xs text-ink/60">{{ $entry->created_at->diffForHumans() }}</p>
                        </a>
                    </li>
                @empty
                    <li class="text-sm text-ink/70">Nothing has happened yet.</li>
                @endforelse
            </ol>
        </section>
    </div>
</x-app-layout>
