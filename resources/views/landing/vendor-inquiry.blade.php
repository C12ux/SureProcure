<section id="vendor-inquiry" class="scroll-mt-6 bg-[#eeecfc] text-ink" aria-labelledby="vendor-inquiry-title">
    <div class="page-container grid items-start gap-8 py-14 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16 lg:py-20">
        <div>
            <h2 id="vendor-inquiry-title" class="heading-section">Want to be a Vendor?</h2>
            <p class="lead mt-4 max-w-lg text-ink/75">
                Tell us what products or services your company supplies. The SureProcure team reviews inquiries and may contact you with an invitation to apply for accreditation.
            </p>
        </div>

        <div class="rounded-2xl border border-jackson/15 bg-white p-5 sm:p-8">
            @if (session('inquiry_received'))
                <p role="status" class="rounded-lg bg-success/20 px-4 py-3 font-medium">We received your vendor inquiry. Thank you for contacting SureProcure.</p>
            @else
                <form method="POST" action="{{ route('vendor-inquiries.store') }}" class="space-y-5">
                    @csrf
                    <div>
                        <x-input-label for="company_name" value="Company name" />
                        <x-text-input id="company_name" class="mt-1.5" name="company_name" :value="old('company_name')" autocomplete="organization" maxlength="255" required />
                        <x-input-error :messages="$errors->get('company_name')" />
                    </div>
                    <div>
                        <x-input-label for="contact_name" value="Contact name" />
                        <x-text-input id="contact_name" class="mt-1.5" name="contact_name" :value="old('contact_name')" autocomplete="name" maxlength="255" required />
                        <x-input-error :messages="$errors->get('contact_name')" />
                    </div>
                    <div>
                        <x-input-label for="email" value="Business email" />
                        <x-text-input id="email" class="mt-1.5" name="email" type="email" :value="old('email')" autocomplete="email" maxlength="255" required />
                        <x-input-error :messages="$errors->get('email')" />
                    </div>
                    <div>
                        <x-input-label for="message" value="Products or services you supply" />
                        <textarea id="message" name="message" class="field-input mt-1.5" rows="4" maxlength="2000" required>{{ old('message') }}</textarea>
                        <x-input-error :messages="$errors->get('message')" />
                    </div>
                    <button type="submit" class="btn btn-lg btn-solid">Send inquiry</button>
                </form>
            @endif
        </div>
    </div>
</section>
