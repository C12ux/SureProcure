<section>
    <header>
        <h2 class="app-card-title">Update password</h2>
        <p class="mt-1 text-sm text-ink/70">Use at least 8 characters, mixing letters, numbers and symbols.</p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 max-w-xl space-y-5" x-data="{ pw: '', confirm: '' }">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Current password" />
            <x-password-input id="update_password_current_password" name="current_password" class="mt-1.5" autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="New password" />
            <x-password-input id="update_password_password" name="password" model="pw" :checklist="true" class="mt-1.5" autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirm new password" />
            <x-password-input id="update_password_password_confirmation" name="password_confirmation" model="confirm" class="mt-1.5" autocomplete="new-password" />
            <p class="field-hint" x-show="confirm.length > 0" x-cloak>
                <span x-show="confirm === pw" class="font-medium text-ink">Passwords match</span>
                <span x-show="confirm !== pw" class="font-medium text-danger-deep">Passwords do not match yet</span>
            </p>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Save</x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2500)"
                   class="text-sm font-medium text-ink/70">Saved.</p>
            @endif
        </div>
    </form>
</section>
