<x-app-layout>
    <x-slot name="title">Dashboard</x-slot>

    <div class="app-card max-w-2xl">
        <h2 class="app-card-title">Your {{ \Illuminate\Support\Str::headline($role->value) }} dashboard is coming next</h2>
        <p class="mt-3 text-ink/70">
            The customer dashboard is being built first. Your account and permissions are already set up,
            so this page will fill in as each role's screens are added.
        </p>
    </div>
</x-app-layout>
