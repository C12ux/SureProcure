<x-app-layout>
    <x-slot name="section">Dashboard</x-slot>
    <x-slot name="title">{{ __('Dashboard') }}</x-slot>

    <section class="app-card">
        <h2 class="app-card-title">{{ __('Account') }}</h2>
        <p class="mt-2 text-sm text-ink/70">{{ __("You're logged in!") }}</p>
    </section>
</x-app-layout>
