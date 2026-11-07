<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    @if (! $vendor)
        <x-alert type="warning">Your account is not linked to a vendor company yet. Please contact SureProcure.</x-alert>
    @else
        <p class="lead text-ink/70">Welcome, {{ strtok($user->name, ' ') }}. {{ $vendor->name }} is on SureProcure.</p>

        <div class="mt-6">@include('vendor._status')</div>

        <section class="app-card max-w-3xl">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h2 class="app-card-title">Accreditation</h2>
                <x-vendor-badge :vendor="$vendor" />
            </div>

            @if ($vendor->accreditation_status === \App\Enums\AccreditationStatus::Accredited)
                <p class="mt-3 text-ink/70">
                    Your company can be invited to quote on customer requests.
                    @if ($vendor->accreditation_expires_at) Accreditation runs until {{ $vendor->accreditation_expires_at->format('F j, Y') }}. @endif
                </p>
            @else
                <p class="mt-3 text-ink/70">Customers can only receive quotes from accredited vendors. Finish the three steps and send your application for review.</p>

                <div class="mt-5">
                    <div class="flex items-baseline justify-between text-sm">
                        <span class="font-semibold">{{ $accreditation->percent() }}% complete</span>
                    </div>
                    <div class="bar-track mt-2" role="progressbar" aria-valuenow="{{ $accreditation->percent() }}" aria-valuemin="0" aria-valuemax="100">
                        <div class="bar-fill bar-fill--best" style="width: {{ $accreditation->percent() }}%; --d: 100ms;"></div>
                    </div>
                </div>

                <a href="{{ route('vendor.accreditation.show') }}" class="btn btn-md btn-solid mt-6">
                    {{ $vendor->accreditation_status === \App\Enums\AccreditationStatus::UnderReview ? 'View your application' : 'Continue accreditation' }}
                </a>
            @endif
        </section>
    @endif
</x-app-layout>
