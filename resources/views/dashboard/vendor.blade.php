<x-app-layout>
    <x-slot name="section">Dashboard</x-slot>
    <x-slot name="description">Manage accreditation, invited requests, and awarded purchase orders.</x-slot>
    <x-slot name="title">Vendor dashboard</x-slot>

    @if (! $vendor)
        <x-alert type="warning">Your account is not linked to a vendor company yet. Please contact SureProcure.</x-alert>
    @else
        @include('vendor._status')

        <section class="priority-panel" aria-labelledby="vendor-priority">
            <div class="min-w-0">
                <p class="priority-label">Accreditation · {{ $vendor->statusLabel() }}</p>
                <h2 id="vendor-priority" class="priority-title">
                    {{ $vendor->isAccredited() ? 'You’re eligible for sourcing' : 'Your accreditation application' }}
                </h2>
                @if ($vendor->isAccredited())
                    <p class="priority-copy">
                        {{ $vendor->name }}
                        @if ($vendor->accreditation_expires_at) · Valid through {{ $vendor->accreditation_expires_at->format('M j, Y') }} @endif
                    </p>
                @else
                    <p class="priority-copy">{{ $accreditation->percent() }}% of the application is complete.</p>
                @endif
            </div>
            <a href="{{ route('vendor.accreditation.show') }}" class="btn btn-sm btn-light">View accreditation</a>
        </section>

        {{-- Your data --}}
        <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat label="Awaiting your quote" :value="$stats['awaiting']" :tone="$stats['awaiting'] > 0 ? 'attention' : 'neutral'" />
            <x-stat label="Invitations received" :value="$stats['invited']" />
            <x-stat label="Quotations submitted" :value="$stats['quoted']" />
            <x-stat label="Orders awarded" :value="$stats['awarded']" />
        </div>
        @if ($stats['awarded'] > 0)
            <p class="mt-3 text-sm text-ink/70">Total awarded value: <span class="font-semibold tabular-nums text-ink">PHP {{ number_format($stats['awarded_value'], 2) }}</span></p>
        @endif

        @if ($metrics['has_data'])
            @include('vendor._metrics')
        @endif

        <div class="mt-8 grid gap-6 2xl:grid-cols-3">

            {{-- Invitations --}}
            <section class="app-card app-card--flush 2xl:col-span-2">
                <div class="flex items-center justify-between px-6 pb-4 pt-6">
                    <h2 class="app-card-title">Requests you were invited to</h2>
                    @if ($rows->isNotEmpty())
                        <a href="{{ route('vendor.requests.index') }}" class="auth-link text-sm">View all</a>
                    @endif
                </div>
                @if ($rows->isEmpty())
                    <p class="border-t border-ink/10 px-6 py-6 text-sm text-ink/70">
                        {{ $vendor->isAccredited()
                            ? 'No invitations yet. When a customer request matches your categories, your procurement agent will invite you and it will appear here.'
                            : 'Invitations appear here once your accreditation is approved.' }}
                    </p>
                @else
                    @include('vendor._requests-table', ['rows' => $rows])
                @endif
            </section>

            <div class="space-y-6">
                {{-- Procurement agents --}}
                <section class="app-card">
                    <h2 class="app-card-title">Your procurement agent</h2>
                    @forelse ($agents as $agent)
                        <div class="mt-4 rounded-xl border border-ink/10 p-4">
                            <p class="font-semibold">{{ $agent['officer']->name }}</p>
                            <a href="mailto:{{ $agent['officer']->email }}" class="auth-link text-sm">{{ $agent['officer']->email }}</a>
                            @if ($agent['officer']->phone)
                                <p class="mt-1 text-sm text-ink/70">{{ $agent['officer']->phone }}</p>
                            @endif
                            <p class="mt-2 text-xs text-ink/60">On {{ implode(', ', array_slice($agent['requests'], 0, 3)) }}@if (count($agent['requests']) > 3) and {{ count($agent['requests']) - 3 }} more @endif</p>
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-ink/70">Your agent’s contact details appear here once you are invited to a request.</p>
                    @endforelse
                </section>

                {{-- Awarded orders, with the customer's contact when it may be shown --}}
                <section class="app-card">
                    <h2 class="app-card-title">Awarded orders</h2>
                    @forelse ($orders as $order)
                        <div class="mt-4 rounded-xl border border-ink/10 p-4">
                            <p class="text-sm font-semibold tabular-nums">{{ $order['po']->po_number }}</p>
                            <p class="mt-0.5">{{ $order['pr']?->title }}</p>
                            <p class="mt-1 text-sm tabular-nums text-ink/70">{{ $order['po']->currency }} {{ number_format((float) $order['po']->amount, 2) }}, issued {{ $order['po']->issued_at?->format('M j, Y') }}</p>

                            @php [$poLabel, $poTone] = \App\Support\VendorPortal::orderStatus($order['po']->status); @endphp
                            <div class="mt-3 flex flex-wrap items-center gap-3">
                                <span class="badge badge--{{ $poTone }}"><span class="badge-dot"></span>{{ $poLabel }}</span>
                                @if ($order['po']->status === \App\Enums\PoStatus::Issued)
                                    <form method="POST" action="{{ route('vendor.orders.acknowledge', $order['po']) }}">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-solid">Acknowledge order</button>
                                    </form>
                                @endif
                            </div>

                            @if ($order['customer'])
                                <div class="mt-3 border-t border-ink/10 pt-3 text-sm">
                                    <p class="text-ink/60">Customer</p>
                                    <p class="font-semibold">{{ $order['customer']['company'] }}</p>
                                    @if ($order['customer']['name'])<p>{{ $order['customer']['name'] }}</p>@endif
                                    @if ($order['customer']['email'])<a href="mailto:{{ $order['customer']['email'] }}" class="auth-link">{{ $order['customer']['email'] }}</a>@endif
                                    @if ($order['customer']['phone'])<p class="text-ink/70">{{ $order['customer']['phone'] }}</p>@endif
                                </div>
                            @endif
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-ink/70">Orders you win appear here{{ $contactNote ? ', with the customer’s contact details' : '' }}.</p>
                    @endforelse
                </section>
            </div>
        </div>
    @endif
</x-app-layout>
