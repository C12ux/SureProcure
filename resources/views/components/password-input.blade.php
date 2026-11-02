{{--
    Password field with a show/hide (eye) button.

    Props
      id, name     the input's id and name
      model        optional: name of a variable on the surrounding form's x-data (e.g. "pw"),
                   so other parts of the form can read what was typed
      checklist    true = show live requirement ticks under the field

    The checklist mirrors the server rule in AppServiceProvider
    (min 8, a letter, a number, a symbol). The server still has the final say.
--}}
@props(['id', 'name', 'model' => null, 'checklist' => false])

@php $bind = $model ?: 'value'; @endphp

<div x-data="{ show: false, value: '' }" {{ $attributes->only('class') }}>
    <div class="relative">
        <input id="{{ $id }}" name="{{ $name }}" type="password" x-bind:type="show ? 'text' : 'password'" x-model="{{ $bind }}"
               {{ $attributes->except('class')->merge(['class' => 'field-input pr-12']) }}>

        <button type="button"
                class="absolute inset-y-0 right-0 flex items-center rounded-r-xl px-3.5 text-ink/60 hover:text-ink"
                aria-label="Show password"
                x-bind:aria-label="show ? 'Hide password' : 'Show password'"
                x-bind:aria-pressed="show"
                x-on:click="show = !show">
            <x-icon name="eye" class="h-5 w-5" x-show="!show" />
            <x-icon name="eye-off" class="h-5 w-5" x-show="show" x-cloak />
        </button>
    </div>

    @if ($checklist)
        <ul class="mt-3 space-y-1.5 text-sm" aria-label="Password requirements">
            <template x-for="rule in [
                { label: 'At least 8 characters', ok: {{ $bind }}.length >= 8 },
                { label: 'A letter', ok: /\p{L}/u.test({{ $bind }}) },
                { label: 'A number', ok: /\p{N}/u.test({{ $bind }}) },
                { label: 'A symbol, such as ! or #', ok: /[\p{Z}\p{S}\p{P}]/u.test({{ $bind }}) },
            ]" x-bind:key="rule.label">
                <li class="flex items-center gap-2" x-bind:class="rule.ok ? 'font-medium text-ink' : 'text-ink/60'">
                    <span class="flex h-4 w-4 shrink-0 items-center justify-center">
                        <x-icon name="check" class="h-4 w-4 text-jackson" x-show="rule.ok" />
                        <span class="h-3 w-3 rounded-full border-2 border-ink/30" x-show="!rule.ok"></span>
                    </span>
                    <span class="sr-only" x-text="rule.ok ? 'Met: ' : 'Not met: '"></span>
                    <span x-text="rule.label"></span>
                </li>
            </template>
        </ul>
    @endif
</div>
