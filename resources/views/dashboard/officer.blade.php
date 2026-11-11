<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <p class="lead text-ink/70">Welcome back, {{ strtok($user->name, ' ') }}. Here are the requests assigned to you.</p>

    <div class="mt-8 grid gap-4 sm:grid-cols-3">
        <a href="{{ route('officer.requisitions.index', ['status' => 'action']) }}" class="block rounded-2xl">
            <x-stat label="Need your action" :value="$stats['action']" :tone="$stats['action'] > 0 ? 'attention' : 'neutral'" />
        </a>
        <a href="{{ route('officer.requisitions.index', ['status' => 'waiting']) }}" class="block rounded-2xl">
            <x-stat label="Waiting on the customer" :value="$stats['waiting']" />
        </a>
        <a href="{{ route('officer.requisitions.index', ['status' => 'completed']) }}" class="block rounded-2xl">
            <x-stat label="Completed" :value="$stats['completed']" />
        </a>
    </div>

    <div class="mt-8 grid gap-6 lg:grid-cols-5">
        <section class="app-card app-card--flush lg:col-span-3">
            <div class="flex items-center justify-between px-6 pb-4 pt-6">
                <h2 class="app-card-title">Needs your action</h2>
                <a href="{{ route('officer.requisitions.index') }}" class="auth-link text-sm">View all</a>
            </div>
            @forelse ($actionList as $pr)
                <a href="{{ route('officer.requisitions.show', $pr) }}" class="flex items-center justify-between gap-4 border-t border-ink/10 px-6 py-4 hover:bg-mist-50">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold tabular-nums">{{ $pr->pr_number }}</p>
                        <p class="truncate">{{ $pr->title }}</p>
                        <p class="text-sm text-ink/60">{{ $pr->organization?->display_name }}</p>
                    </div>
                    <x-status-badge :status="$pr->status" :staff="true" />
                </a>
            @empty
                <p class="border-t border-ink/10 px-6 py-6 text-sm text-ink/70">Nothing needs you right now. New requests show up here once an admin assigns them to you.</p>
            @endforelse
        </section>

        <section class="app-card lg:col-span-2">
            <h2 class="app-card-title">Waiting on the customer</h2>
            @forelse ($waitingList as $pr)
                <a href="{{ route('officer.requisitions.show', $pr) }}" class="mt-4 block rounded-xl border border-ink/10 p-4 hover:border-ink/30">
                    <p class="text-sm font-semibold tabular-nums">{{ $pr->pr_number }}</p>
                    <p class="mt-0.5">{{ $pr->title }}</p>
                    <x-status-badge :status="$pr->status" :staff="true" class="mt-3" />
                </a>
            @empty
                <p class="mt-3 text-sm text-ink/70">Nothing is waiting on a customer.</p>
            @endforelse
        </section>
    </div>
</x-app-layout>
