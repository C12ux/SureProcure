{{-- A read-only text box with a Copy button. Used for invitation links. --}}
@props(['value'])

<div x-data="{
        copied: false,
        copy() {
            navigator.clipboard.writeText(@js($value)).then(() => {
                this.copied = true;
                setTimeout(() => this.copied = false, 2000);
            });
        }
     }"
     {{ $attributes->merge(['class' => 'flex flex-col gap-2 sm:flex-row']) }}>
    <input type="text" readonly value="{{ $value }}" class="field-input" aria-label="Invitation link" x-on:focus="$event.target.select()">
    <button type="button" class="btn btn-md btn-solid inline-flex shrink-0 items-center justify-center gap-2" x-on:click="copy()">
        <x-icon name="copy" class="h-4 w-4" />
        <span x-text="copied ? 'Copied' : 'Copy link'">Copy link</span>
    </button>
</div>
