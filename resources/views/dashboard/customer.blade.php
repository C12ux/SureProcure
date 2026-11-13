<x-app-layout>
    <x-slot name="section">Dashboard</x-slot>
    <x-slot name="description">Track purchase requests and review decisions from your procurement officer.</x-slot>
    <x-slot name="title">Customer dashboard</x-slot>
    <x-slot name="actions">
        <a href="{{ route('requisitions.create') }}" class="btn btn-sm btn-solid inline-flex items-center gap-2">
            <x-icon name="plus" class="h-4 w-4" /> New requisition
        </a>
    </x-slot>

    @if ($organization) Here is where {{ $organization->display_name }} stands. @endif
    </p>

    @if ($organization && ! $subscribed)
        <x-alert type="warning" class="mt-6">
            Your account has no active subscription. You can prepare drafts now, but an active subscription is needed to submit requests.
        </x-alert>
    @endif

    @if ($nextRequest = $attention->first())
        <section class="priority-panel" aria-labelledby="customer-priority">
            <div class="min-w-0">
                <p class="priority-label">{{ $nextRequest->status->label() }}</p>
                <h2 id="customer-priority" class="priority-title">{{ $nextRequest->title }}</h2>
                <p class="priority-copy">{{ $nextRequest->pr_number }} · Open this request to see what your procurement officer needs.</p>
            </div>
            <div class="priority-actions">
                <a href="{{ route('requisitions.show', $nextRequest) }}" class="btn btn-sm btn-light">
                    {{ $nextRequest->status === \App\Enums\PrStatus::AwaitingClientReview ? 'Review evaluation' : 'View request' }}
                </a>
            </div>
        </section>
    @endif

    {{-- Counts --}}
    <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        <a href="{{ route('requisitions.index', ['status' => 'drafts']) }}" class="block rounded-2xl">
            <x-stat label="Drafts" :value="$stats['drafts']" />
        </a>
        <a href="{{ route('requisitions.index', ['status' => 'in_progress']) }}" class="block rounded-2xl">
            <x-stat label="In progress" :value="$stats['in_progress']" />
        </a>
        <a href="{{ route('requisitions.index', ['status' => 'attention']) }}" class="block rounded-2xl">
            <x-stat label="Needs your attention" :value="$stats['attention']" :tone="$stats['attention'] > 0 ? 'attention' : 'neutral'" />
        </a>
        <a href="{{ route('requisitions.index', ['status' => 'completed']) }}" class="block rounded-2xl">
            <x-stat label="Completed" :value="$stats['completed']" />
        </a>
    </div>

    @if ($recent->isEmpty())
        <x-empty-state class="mt-8" title="No requisitions yet" text="Describe what you need and save it as a draft. Submit it when you are ready and your procurement officer takes it from there.">
            <a href="{{ route('requisitions.create') }}" class="btn btn-md btn-solid">Create your first requisition</a>
        </x-empty-state>
    @else
        <div class="mt-8 grid gap-6 2xl:grid-cols-3">

            <section class="app-card app-card--flush 2xl:col-span-2">
                <div class="flex items-center justify-between px-6 pb-4 pt-6">
                    <h2 class="app-card-title">Recent requisitions</h2>
                    <a href="{{ route('requisitions.index') }}" class="auth-link text-sm">View all</a>
                </div>
                @include('requisitions._table', ['rows' => $recent])
            </section>

            <div class="space-y-6">
                <section class="app-card">
                    <h2 class="app-card-title">Needs your attention</h2>
                    @forelse ($attention as $pr)
                        <a href="{{ route('requisitions.show', $pr) }}" class="mt-4 block rounded-xl border border-ink/10 p-4 hover:border-ink/30">
                            <p class="text-sm font-semibold tabular-nums">{{ $pr->pr_number }}</p>
                            <p class="mt-0.5">{{ $pr->title }}</p>
                            <x-status-badge :status="$pr->status" class="mt-3" />
                        </a>
                    @empty
                        <p class="mt-3 text-sm text-ink/70">Nothing is waiting on you.</p>
                    @endforelse
                </section>

                <section class="app-card">
                    <h2 class="app-card-title">Your procurement data</h2>
                    <p class="mt-2 text-sm text-ink/70">Download every requisition as a CSV file that opens in Excel.</p>
                    <a href="{{ route('requisitions.export') }}" class="btn btn-md btn-outline-dark mt-4 inline-flex items-center gap-2">
                        <x-icon name="download" class="h-4 w-4" /> Download data
                    </a>
                </section>
            </div>
        </div>
    @endif
</x-app-layout>
