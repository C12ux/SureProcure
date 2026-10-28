<x-guest-layout>
    <h1 class="heading-form">Reset your password</h1>
    <p class="mt-2 text-base text-ink/70">Enter your email and we will send you a link to choose a new password.</p>

    <x-auth-session-status class="mt-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="mt-8 space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">Email reset link</x-primary-button>
    </form>

    <p class="mt-8 text-sm"><a href="{{ route('login') }}" class="auth-link">Back to log in</a></p>
</x-guest-layout>
