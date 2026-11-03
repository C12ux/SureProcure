<x-app-layout>
    <x-slot name="title">Invite someone</x-slot>

    <form method="POST" action="{{ route('admin.invitations.store') }}" class="app-card max-w-2xl space-y-6"
          x-data="{ role: '{{ old('role', 'vendor') }}' }">
        @csrf

        <fieldset>
            <legend class="field-label">Who are you inviting?</legend>
            <div class="mt-2 grid gap-3 sm:grid-cols-2">
                <label class="flex cursor-pointer gap-3 rounded-xl border p-4"
                       x-bind:class="role === 'vendor' ? 'border-jackson bg-jackson/5' : 'border-ink/15'">
                    <input type="radio" name="role" value="vendor" x-model="role" class="field-check mt-1 rounded-full">
                    <span>
                        <span class="block font-semibold">Vendor</span>
                        <span class="block text-sm text-ink/70">Gets accredited, then quotes on customer requests.</span>
                    </span>
                </label>
                <label class="flex cursor-pointer gap-3 rounded-xl border p-4"
                       x-bind:class="role === 'procurement_officer' ? 'border-jackson bg-jackson/5' : 'border-ink/15'">
                    <input type="radio" name="role" value="procurement_officer" x-model="role" class="field-check mt-1 rounded-full">
                    <span>
                        <span class="block font-semibold">Procurement officer</span>
                        <span class="block text-sm text-ink/70">Runs customer requests from review to purchase order.</span>
                    </span>
                </label>
            </div>
            <x-input-error :messages="$errors->get('role')" />
        </fieldset>

        <div>
            <x-input-label for="email" value="Email address" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email" :value="old('email')" required autofocus />
            <p class="field-hint">Double-check the spelling. The link only works for the account created with this email.</p>
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div x-show="role === 'vendor'" x-cloak>
            <x-input-label for="company_name" value="Company name" />
            <x-text-input id="company_name" class="mt-1.5" type="text" name="company_name" :value="old('company_name')" x-bind:required="role === 'vendor'" />
            <x-input-error :messages="$errors->get('company_name')" />
        </div>

        <p class="text-sm text-ink/70">You will get a link to copy and send. It works for 14 days.</p>

        <div class="flex items-center gap-3">
            <button type="submit" class="btn btn-lg btn-solid">Create invitation</button>
            <a href="{{ route('admin.invitations.index') }}" class="auth-link ml-2 text-sm">Cancel</a>
        </div>
    </form>
</x-app-layout>
