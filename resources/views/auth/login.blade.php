<x-guest-layout>
    <h1 class="heading-form">Log in</h1>
    <p class="mt-2 text-base text-ink/70">Welcome back. Pick up where your requests left off.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password" required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div class="flex items-center justify-between gap-4">
            <label for="remember_me" class="inline-flex items-center gap-2 text-sm">
                <input id="remember_me" type="checkbox" class="field-check" name="remember">
                <span>Keep me logged in</span>
            </label>

            @if (Route::has('password.request'))
                <a class="auth-link text-sm" href="{{ route('password.request') }}">Forgot your password?</a>
            @endif
        </div>

        <x-primary-button class="w-full">Log in</x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-8 text-sm">
            New customer? <a href="{{ route('register') }}" class="auth-link">Create an account</a>
        </p>
    @endif
    <p class="mt-3 text-sm text-ink/70">Vendors and procurement officers: log in with the email your invitation was sent to.</p>
</x-guest-layout>
