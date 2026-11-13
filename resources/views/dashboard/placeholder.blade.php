<x-app-layout>
    <x-slot name="section">Dashboard</x-slot>
    <x-slot name="title">Workspace unavailable</x-slot>
    <x-slot name="description">This workspace is not available for your role yet.</x-slot>

    <section class="app-card max-w-2xl">
        <h2 class="app-card-title">{{ \Illuminate\Support\Str::headline($role->value) }}</h2>
        <p class="mt-2 text-sm text-ink/70">Contact your administrator about your assigned role if you need access.</p>
    </section>
</x-app-layout>
