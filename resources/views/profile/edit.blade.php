<x-app-layout>
    <x-slot name="section">Profile</x-slot>
    <x-slot name="title">{{ __('Profile') }}</x-slot>
    <x-slot name="description">{{ __('Manage your account details, password, and account access.') }}</x-slot>

    <div class="max-w-4xl space-y-5">
        <section class="app-card">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </section>

        <section class="app-card">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </section>

        <section class="app-card border-danger-deep/20">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </section>
    </div>
</x-app-layout>
