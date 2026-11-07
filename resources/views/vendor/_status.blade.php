
{{-- Status message for a vendor's application. Requires $vendor. --}}

@use('App\Enums\AccreditationStatus')

@php
    $state = $vendor->accreditation_status;
@endphp

@if ($state === AccreditationStatus::Accredited && $vendor->isAccredited())
    <x-alert type="success">
        You are accredited.

        @if ($vendor->accreditation_expires_at)
            Valid until {{ $vendor->accreditation_expires_at->format('F j, Y') }}.
        @endif

        @if ($vendor->remarks)
            <span class="mt-1 block font-normal">
                {{ $vendor->remarks }}
            </span>
        @endif
    </x-alert>

@elseif ($state === AccreditationStatus::Accredited)
    <x-alert type="warning">
        Your accreditation has expired. Contact SureProcure to renew it.
    </x-alert>

@elseif ($state === AccreditationStatus::UnderReview)
    <x-alert type="info">
        Your application is under review. You cannot edit it until SureProcure responds.
    </x-alert>

@elseif ($state === AccreditationStatus::Rejected)
    <x-alert type="error">
        Your application was rejected.

        @if ($vendor->rejection_reason)
            <span class="mt-1 block font-normal">
                Reason: {{ $vendor->rejection_reason }}
            </span>
        @endif

        <span class="mt-1 block font-normal">
            You can update your details and submit again.
        </span>
    </x-alert>

@elseif ($state === AccreditationStatus::Pending && $vendor->submitted_at)
    <x-alert type="warning">
        SureProcure asked for changes.

        @if ($vendor->remarks)
            <span class="mt-1 block font-normal">
                {{ $vendor->remarks }}
            </span>
        @endif
    </x-alert>
@endif
