@php
    $answer = fn ($value, $name) => (string) old($name, is_null($value) ? '' : (int) $value);
@endphp

<x-app-layout>
    <x-slot name="section">Accreditation</x-slot>
    <x-slot name="description">Update your experience, certifications, and compliance information.</x-slot>
    <x-slot name="title">Capability and compliance</x-slot>
    <x-slot name="actions"><a href="{{ route('vendor.accreditation.show') }}" class="btn btn-sm btn-outline-dark">Back</a></x-slot>

    <form method="POST" action="{{ route('vendor.accreditation.capability.save') }}" class="max-w-4xl space-y-6"
          x-data="{ disputes: '{{ $answer($vendor->has_legal_disputes, 'has_legal_disputes') }}', hse: '{{ $answer($vendor->complies_hse, 'complies_hse') }}' }">
        @csrf
        @method('PUT')

        {{-- G. Experience and operations --}}
        <section class="app-card space-y-5">
            <h2 class="app-card-title">Experience and operations</h2>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="years_in_industry" value="Years in industry" />
                    <x-text-input id="years_in_industry" class="mt-1.5" name="years_in_industry" type="number" min="0" :value="old('years_in_industry', $vendor->years_in_industry)" />
                    <x-input-error :messages="$errors->get('years_in_industry')" />
                </div>
                <div>
                    <x-input-label for="employees_count" value="Number of employees" />
                    <x-text-input id="employees_count" class="mt-1.5" name="employees_count" type="number" min="0" :value="old('employees_count', $vendor->employees_count)" />
                    <x-input-error :messages="$errors->get('employees_count')" />
                </div>
            </div>

            <fieldset>
                <legend class="field-label">Industries served</legend>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($industryOptions as $industry)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="industries[]" value="{{ $industry }}" class="field-check" @checked(in_array($industry, $selectedIndustries, true))>
                            {{ $industry }}
                        </label>
                    @endforeach
                </div>
                <div class="mt-3">
                    <x-input-label for="industry_other" value="Others" />
                    <x-text-input id="industry_other" class="mt-1.5" name="industry_other" type="text" :value="$industryOther" />
                </div>
                <x-input-error :messages="$errors->get('industries')" />
            </fieldset>

            <div>
                <x-input-label for="service_capacity" value="Production capacity / service capacity" />
                <textarea id="service_capacity" name="service_capacity" rows="3" class="field-input mt-1.5">{{ old('service_capacity', $vendor->service_capacity) }}</textarea>
                <x-input-error :messages="$errors->get('service_capacity')" />
            </div>

            <div>
                <x-input-label for="facilities_equipment" value="Facilities / equipment" />
                <textarea id="facilities_equipment" name="facilities_equipment" rows="3" class="field-input mt-1.5">{{ old('facilities_equipment', $vendor->facilities_equipment) }}</textarea>
                <x-input-error :messages="$errors->get('facilities_equipment')" />
            </div>
        </section>

        {{-- G.3 Quality certifications --}}
        <section class="app-card space-y-4">
            <h2 class="app-card-title">Quality certifications</h2>
            <div class="grid gap-2 sm:grid-cols-3">
                @foreach ($isoOptions as $iso)
                    <label class="flex items-center gap-2 text-sm">
                        <input type="checkbox" name="iso[]" value="{{ $iso }}" class="field-check" @checked(in_array($iso, $selectedIso, true))>
                        {{ $iso }}
                    </label>
                @endforeach
            </div>
            <div>
                <x-input-label for="other_certifications" value="Other certifications" />
                <textarea id="other_certifications" name="other_certifications" rows="3" class="field-input mt-1.5" placeholder="One per line">{{ $otherCertifications }}</textarea>
                <x-input-error :messages="$errors->get('other_certifications')" />
            </div>
            <p class="field-hint">Upload the certificates themselves on the Documents step.</p>
        </section>

        {{-- H. Compliance and risk --}}
        <section class="app-card space-y-6">
            <h2 class="app-card-title">Compliance and risk</h2>

            <div>
                <x-yes-no name="has_legal_disputes" model="disputes" :value="$vendor->has_legal_disputes"
                          label="In the last 5 years, has your company been involved in any legal disputes or government sanctions?" />
                <div class="mt-3" x-show="disputes === '1'" x-cloak>
                    <x-input-label for="legal_disputes_details" value="Please give details" />
                    <textarea id="legal_disputes_details" name="legal_disputes_details" rows="3" class="field-input mt-1.5">{{ old('legal_disputes_details', $vendor->legal_disputes_details) }}</textarea>
                    <x-input-error :messages="$errors->get('legal_disputes_details')" />
                </div>
            </div>

            <div>
                <x-yes-no name="complies_hse" model="hse" :value="$vendor->complies_hse"
                          label="Do you comply with national health, safety and environment (HSE) standards?" />
                <div class="mt-3" x-show="hse === '1'" x-cloak>
                    <x-input-label for="hse_details" value="Certifications or policies" />
                    <textarea id="hse_details" name="hse_details" rows="3" class="field-input mt-1.5">{{ old('hse_details', $vendor->hse_details) }}</textarea>
                    <x-input-error :messages="$errors->get('hse_details')" />
                </div>
            </div>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-lg btn-solid">Save</button>
            <a href="{{ route('vendor.accreditation.show') }}" class="auth-link ml-2 text-sm">Cancel</a>
        </div>
    </form>
</x-app-layout>
