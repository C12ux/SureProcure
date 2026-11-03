<x-guest-layout>
    <h1 class="heading-form">Welcome to SureProcure</h1>

    <p class="mt-2 text-base text-ink/70">
        @if ($invitation->role === \App\Enums\UserRole::Vendor)
            You have been invited to join as a vendor for <span class="font-semibold text-ink">{{ $invitation->company_name }}</span>.
            Set a password to create your account. You will complete your accreditation next.
        @else
            You have been invited to join as a procurement officer. Set a password to create your account.
        @endif
    </p>

    <form method="POST" action="{{ route('invitations.accept.store', $token) }}" class="mt-8 space-y-5" x-data="{ pw: '', confirm: '' }">
        @csrf

        <div>
            <span class="field-label">Email</span>
            <p class="mt-1.5 rounded-xl border border-ink/10 bg-mist-100 px-4 py-3 text-base">{{ $invitation->email }}</p>
        </div>

        <div>
            <x-input-label for="name" value="Your name" />
            <x-text-input id="name" class="mt-1.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="phone" value="Phone number (optional)" />
            <x-text-input id="phone" class="mt-1.5" type="tel" name="phone" :value="old('phone')" autocomplete="tel" />
            <x-input-error :messages="$errors->get('phone')" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-password-input id="password" name="password" model="pw" :checklist="true" class="mt-1.5" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-password-input id="password_confirmation" name="password_confirmation" model="confirm" class="mt-1.5" required autocomplete="new-password" />
            <p class="field-hint" x-show="confirm.length > 0" x-cloak>
                <span x-show="confirm === pw" class="font-medium text-ink">Passwords match</span>
                <span x-show="confirm !== pw" class="font-medium text-danger-deep">Passwords do not match yet</span>
            </p>
        </div>

        <x-primary-button class="w-full">Create my account</x-primary-button>
    </form>
</x-guest-layout>
