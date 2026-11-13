<x-guest-layout>
    <h1 class="heading-form text-jackson">This link can’t be used</h1>
    <p class="mt-3 text-base text-ink/70">{{ $message }}</p>

    <p class="mt-8 text-sm">
        <a href="{{ route('login') }}" class="auth-link">Go to log in</a>
    </p>
</x-guest-layout>
