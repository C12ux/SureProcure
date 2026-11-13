@use('App\Enums\AccreditationStatus')

@php
    $steps = [
        'company' => ['route' => 'vendor.accreditation.company', 'text' => 'Business details, contacts, tax information, products, categories and clients.'],
        'capability' => ['route' => 'vendor.accreditation.capability', 'text' => 'Experience, operations, quality certifications, legal and safety compliance.'],
        'documents' => ['route' => 'vendor.accreditation.documents', 'text' => 'Registrations, permits, financial statements, references and your signed code of ethics.'],
    ];
    $canSubmit = $canEdit && in_array($vendor->accreditation_status, [AccreditationStatus::Pending, AccreditationStatus::Rejected], true);
@endphp

<x-app-layout>
    <x-slot name="section">Accreditation</x-slot>
    <x-slot name="description">Complete your company profile, capability information, and supporting documents.</x-slot>
    <x-slot name="title">Accreditation</x-slot>
    <x-slot name="actions"><x-vendor-badge :vendor="$vendor" /></x-slot>

    @include('vendor._status')

    <div class="max-w-3xl">
        <p class="lead text-ink/70">Customers only receive quotes from accredited vendors. Complete the three steps below, then send your application for review. You can save as you go.</p>

        <div class="mt-6">
            <p class="text-sm font-semibold">{{ $accreditation->percent() }}% complete</p>
            <div class="bar-track mt-2" role="progressbar" aria-valuenow="{{ $accreditation->percent() }}" aria-valuemin="0" aria-valuemax="100">
                <div class="bar-fill bar-fill--best" style="width: {{ $accreditation->percent() }}%; --d: 100ms;"></div>
            </div>
        </div>

        <ol class="mt-8 space-y-4">
            @foreach ($accreditation->sections() as $key => $section)
                <li class="app-card flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                    <div class="min-w-0">
                        <div class="flex items-center gap-3">
                            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $section['done'] ? 'bg-jackson text-mist-50' : 'border-2 border-ink/20 font-display font-bold' }}">
                                @if ($section['done'])
                                    <x-brand.check class="h-4 w-4" />
                                    <span class="sr-only">Complete</span>
                                @else
                                    {{ $loop->iteration }}
                                @endif
                            </span>
                            <h2 class="app-card-title">{{ $section['label'] }}</h2>
                        </div>
                        <p class="mt-2 text-sm text-ink/70">{{ $steps[$key]['text'] }}</p>
                        @if (! $section['done'])
                            <p class="mt-3 text-sm"><span class="font-semibold">Still needed:</span> {{ implode(', ', array_slice($section['missing'], 0, 4)) }}@if (count($section['missing']) > 4) and {{ count($section['missing']) - 4 }} more @endif</p>
                        @endif
                    </div>
                    <a href="{{ route($steps[$key]['route']) }}" class="btn btn-md shrink-0 {{ $canEdit ? ($section['done'] ? 'btn-outline-dark' : 'btn-solid') : 'btn-outline-dark' }}">
                        {{ $canEdit ? ($section['done'] ? 'Review' : 'Fill in') : 'View' }}
                    </a>
                </li>
            @endforeach
        </ol>

        @if ($canSubmit)
            <section class="app-card mt-8">
                <h2 class="app-card-title">Declaration and submission</h2>

                @if ($accreditation->isComplete())
                    <form method="POST" action="{{ route('vendor.accreditation.submit') }}" class="mt-4 space-y-5">
                        @csrf
                        <label class="flex gap-3 text-sm">
                            <input type="checkbox" name="certify" value="1" class="field-check mt-0.5" @checked(old('certify'))>
                            <span>I certify that all information provided is true and correct. I understand that submitting this form does not guarantee accreditation and that SureProcure may verify all information.</span>
                        </label>
                        <x-input-error :messages="$errors->get('certify')" />

                        <div>
                            <x-input-label for="declared_by" value="Type your full name as your signature" />
                            <x-text-input id="declared_by" class="mt-1.5 max-w-md" name="declared_by" type="text" :value="old('declared_by', $vendor->representative_name)" required />
                            <x-input-error :messages="$errors->get('declared_by')" />
                        </div>

                        <button type="submit" class="btn btn-lg btn-solid">Submit for review</button>
                    </form>
                @else
                    <p class="mt-3 text-sm text-ink/70">You can submit once all three steps are complete.</p>
                @endif
            </section>
        @endif
    </div>
</x-app-layout>
