<x-guest-layout>
    <h1 class="heading-form">Create your account</h1>
    <p class="mt-2 text-base text-ink/70">Set up your company, then lodge purchase requisitions. A subscription is needed to submit requests.</p>

    <form method="POST" action="{{ route('register') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Your name" />
            <x-text-input id="name" class="mt-1.5" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="company_name" value="Company name" />
            <x-text-input id="company_name" class="mt-1.5" type="text" name="company_name" :value="old('company_name')" required autocomplete="organization" />
            <x-input-error :messages="$errors->get('company_name')" />
        </div>

        <div>
            <x-input-label for="email" value="Work email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirm password" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation" required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">Create account</x-primary-button>
    </form>

    <p class="mt-8 text-sm">
        Already registered? <a href="{{ route('login') }}" class="auth-link">Log in</a>
    </p>
    <p class="mt-3 text-sm text-ink/70">Vendors and procurement officers join by invitation.</p>
</x-guest-layout>
