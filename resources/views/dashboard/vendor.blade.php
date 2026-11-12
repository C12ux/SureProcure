@use('App\Enums\AccreditationStatus')

<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @if (! $vendor)
        <x-alert type="warning">Your account is not linked to a vendor company yet. Please contact SureProcure.</x-alert>
    @else
        <p class="lead text-ink/70">Welcome, {{ strtok($user->name, ' ') }}. {{ $vendor->name }} is on SureProcure.</p>

        <div class="mt-6">@include('vendor._status')</div>

        {{-- Accreditation --}}
        <section class="app-card max-w-3xl">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="app-card-title">Accreditation</h2>
                <x-vendor-badge :vendor="$vendor" />
            </div>

            @if ($vendor->accreditation_status === AccreditationStatus::Accredited)
                <p class="mt-3 text-ink/70">
                    Your company can be invited to quote on customer requests.
                    @if ($vendor->accreditation_expires_at) Accreditation runs until {{ $vendor->accreditation_expires_at->format('F j, Y') }}. @endif
                </p>
            @else
                <p class="mt-3 text-ink/70">Customers can only receive quotes from accredited vendors. Finish the three steps and send your application for review.</p>

                <div class="mt-5">
                    <p class="text-sm font-semibold">{{ $accreditation->percent() }}% complete</p>
                    <div class="bar-track mt-2" role="progressbar" aria-valuenow="{{ $accreditation->percent() }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="bar-fill bar-fill--best" style="width: {{ $accreditation->percent() }}%; --d: 100ms;"></div>
                    </div>
                </div>

                <a href="{{ route('vendor.accreditation.show') }}" class="btn btn-md btn-solid mt-6">
                    {{ $vendor->accreditation_status === AccreditationStatus::UnderReview ? 'View your application' : 'Continue accreditation' }}
                </a>
            @endif
        </section>

        {{-- Your data --}}
        <div class="mt-8 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <x-stat label="Awaiting your quote" :value="$stats['awaiting']" :tone="$stats['awaiting'] > 0 ? 'attention' : 'neutral'" />
            <x-stat label="Invitations received" :value="$stats['invited']" />
            <x-stat label="Quotations submitted" :value="$stats['quoted']" />
            <x-stat label="Orders awarded" :value="$stats['awarded']" />
        </div>
        @if ($stats['awarded'] > 0)
            <p class="mt-3 text-sm text-ink/70">Total awarded value: <span class="font-semibold tabular-nums text-ink">PHP {{ number_format($stats['awarded_value'], 2) }}</span></p>
        @endif

        <div class="mt-8 grid gap-6 lg:grid-cols-3">

            {{-- Invitations --}}
            <section class="app-card app-card--flush lg:col-span-2">
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
