<x-app-layout>
    <x-slot name="title">Company profile</x-slot>
    <x-slot name="actions"><a href="{{ route('vendor.accreditation.show') }}" class="btn btn-sm btn-outline-dark">Back</a></x-slot>

    <form method="POST" action="{{ route('vendor.accreditation.company.save') }}" class="max-w-4xl space-y-6"
          x-data="{ clients: @js($clientRows) }">
        @csrf
        @method('PUT')

        {{-- A. Company information --}}
        <section class="app-card space-y-5">
            <h2 class="app-card-title">Company information</h2>

            <div>
                <span class="field-label">Registered business name</span>
                <p class="mt-1.5 rounded-xl border border-ink/10 bg-mist-100 px-4 py-3">{{ $organization->legal_name }}</p>
                <p class="field-hint">Set by your invitation. Contact SureProcure if it needs correcting.</p>
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="trade_name" value="Trade name (if any)" />
                    <x-text-input id="trade_name" class="mt-1.5" name="trade_name" type="text" :value="old('trade_name', $organization->trade_name)" />
                    <x-input-error :messages="$errors->get('trade_name')" />
                </div>
                <div>
                    <x-input-label for="website" value="Website" />
                    <x-text-input id="website" class="mt-1.5" name="website" type="url" placeholder="https://" :value="old('website', $organization->website)" />
                    <x-input-error :messages="$errors->get('website')" />
                </div>
                <div>
                    <x-input-label for="year_established" value="Year established" />
                    <x-text-input id="year_established" class="mt-1.5" name="year_established" type="number" min="1800" :max="now()->year" :value="old('year_established', $organization->year_established)" />
                    <x-input-error :messages="$errors->get('year_established')" />
                </div>
                <div>
                    <x-input-label for="org_type" value="Type of organization" />
                    <select id="org_type" name="org_type" class="field-input mt-1.5">
                        <option value="">Choose one</option>
                        @foreach ($orgTypes as $value => $label)
                            <option value="{{ $value }}" @selected(old('org_type', $organization->org_type) === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('org_type')" />
                </div>
            </div>

            <div>
                <x-input-label for="business_address" value="Business address" />
                <textarea id="business_address" name="business_address" rows="2" class="field-input mt-1.5">{{ old('business_address', $organization->business_address) }}</textarea>
                <x-input-error :messages="$errors->get('business_address')" />
            </div>
        </section>

        {{-- B. Contact details --}}
        <section class="app-card space-y-5">
            <h2 class="app-card-title">Contact details</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="representative_name" value="Authorized representative" />
                    <x-text-input id="representative_name" class="mt-1.5" name="representative_name" type="text" :value="old('representative_name', $vendor->representative_name)" />
                    <x-input-error :messages="$errors->get('representative_name')" />
                </div>
                <div>
                    <x-input-label for="representative_title" value="Position / title" />
                    <x-text-input id="representative_title" class="mt-1.5" name="representative_title" type="text" :value="old('representative_title', $vendor->representative_title)" />
                    <x-input-error :messages="$errors->get('representative_title')" />
                </div>
                <div>
                    <x-input-label for="mobile_number" value="Mobile number" />
                    <x-text-input id="mobile_number" class="mt-1.5" name="mobile_number" type="tel" :value="old('mobile_number', $vendor->mobile_number)" />
                    <x-input-error :messages="$errors->get('mobile_number')" />
                </div>
                <div>
                    <x-input-label for="landline" value="Landline (optional)" />
                    <x-text-input id="landline" class="mt-1.5" name="landline" type="tel" :value="old('landline', $vendor->landline)" />
                    <x-input-error :messages="$errors->get('landline')" />
                </div>
            </div>
            <div>
                <span class="field-label">Email address</span>
                <p class="mt-1.5 rounded-xl border border-ink/10 bg-mist-100 px-4 py-3">{{ $organization->email }}</p>
            </div>
        </section>

        {{-- D. Financial and tax --}}
        <section class="app-card space-y-5">
            <h2 class="app-card-title">Financial and tax information</h2>
            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <x-input-label for="tin" value="TIN" />
                    <x-text-input id="tin" class="mt-1.5" name="tin" type="text" :value="old('tin', $organization->tin)" />
                    <x-input-error :messages="$errors->get('tin')" />
                </div>
                <x-yes-no name="vat_registered" label="VAT registered" :value="$vendor->vat_registered" />
            </div>
            <div>
                <x-input-label for="bank_reference" value="Bank reference" />
                <x-text-input id="bank_reference" class="mt-1.5" name="bank_reference" type="text" placeholder="Bank name, branch and account name" :value="old('bank_reference', $vendor->bank_reference)" />
                <x-input-error :messages="$errors->get('bank_reference')" />
            </div>
        </section>

        {{-- E. Products and services --}}
        <section class="app-card space-y-5">
            <h2 class="app-card-title">Products and services</h2>
            <div>
                <x-input-label for="products_services" value="What do you offer?" />
                <textarea id="products_services" name="products_services" rows="4" class="field-input mt-1.5" placeholder="List your primary offerings relevant to procurement.">{{ old('products_services', $vendor->products_services) }}</textarea>
                <x-input-error :messages="$errors->get('products_services')" />
            </div>

            <fieldset>
                <legend class="field-label">Categories you serve</legend>
                <p class="field-hint">Customers are matched to vendors by category. Pick every one that applies.</p>
                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                    @foreach ($categories as $category)
                        <label class="flex items-center gap-2 text-sm">
                            <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" class="field-check" @checked(in_array($category->id, $selectedCategories, true))>
                            {{ $category->name }}
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('category_ids')" />
            </fieldset>
        </section>

        {{-- F. Client portfolio --}}
        <section class="app-card">
            <h2 class="app-card-title">Client portfolio</h2>
            <p class="field-hint">List your top clients. At least 3 are needed, up to 5.</p>
            <x-input-error :messages="$errors->get('clients')" />

            <div class="mt-5 space-y-4">
                <template x-for="(client, index) in clients" x-bind:key="index">
                    <div class="rounded-xl border border-ink/10 bg-mist-50 p-4">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label class="field-label" x-bind:for="`client-name-${index}`">Client</label>
                                <input class="field-input mt-1.5" type="text" x-bind:id="`client-name-${index}`" x-bind:name="`clients[${index}][client_name]`" x-model="client.client_name">
                            </div>
                            <div>
                                <label class="field-label" x-bind:for="`client-project-${index}`">What you supplied</label>
                                <input class="field-input mt-1.5" type="text" x-bind:id="`client-project-${index}`" x-bind:name="`clients[${index}][project_description]`" x-model="client.project_description">
                            </div>
                        </div>
                        <div class="mt-3 text-right" x-show="clients.length > 1">
                            <button type="button" class="auth-link text-sm" x-on:click="clients.splice(index, 1)">Remove</button>
                        </div>
                    </div>
                </template>
            </div>

            <button type="button" class="btn btn-md btn-outline-dark mt-5" x-show="clients.length < 5"
                    x-on:click="clients.push({ client_name: '', project_description: '' })">Add another client</button>
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" class="btn btn-lg btn-solid">Save</button>
            <a href="{{ route('vendor.accreditation.show') }}" class="auth-link ml-2 text-sm">Cancel</a>
        </div>
    </form>
</x-app-layout>
