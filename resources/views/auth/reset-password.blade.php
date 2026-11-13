<x-guest-layout>
    <h1 class="heading-form text-jackson">Choose a new password</h1>

    <form method="POST" action="{{ route('password.store') }}" class="mt-8 space-y-5" x-data="{ pw: '', confirm: '' }">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email', $request->email)" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="New password" />
            <x-password-input id="password" name="password" model="pw" :checklist="true" class="mt-1.5" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm new password" />
            <x-password-input id="password_confirmation" name="password_confirmation" model="confirm" class="mt-1.5" required autocomplete="new-password" />
            <p class="field-hint" x-show="confirm.length > 0" x-cloak>
                <span x-show="confirm === pw" class="font-medium text-ink">Passwords match</span>
                <span x-show="confirm !== pw" class="font-medium text-danger-deep">Passwords do not match yet</span>
            </p>
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">Save new password</x-primary-button>
    </form>
</x-guest-layout>
