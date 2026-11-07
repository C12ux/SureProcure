{{--
    A Yes / No question. $value is the saved answer (true, false or null); old input wins after a failed save.
    Pass model="name" to tie the answer to a variable in the surrounding x-data (for showing follow-up fields).
--}}
@props(['name', 'label', 'value' => null, 'model' => null])

@php $current = (string) old($name, is_null($value) ? '' : (int) $value); @endphp

<fieldset {{ $attributes }}>
    <legend class="field-label">{{ $label }}</legend>
    <div class="mt-2 flex gap-6">
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="radio" name="{{ $name }}" value="1" class="field-check rounded-full" @checked($current === '1') @if ($model) x-model="{{ $model }}" @endif>
            Yes
        </label>
        <label class="inline-flex items-center gap-2 text-sm">
            <input type="radio" name="{{ $name }}" value="0" class="field-check rounded-full" @checked($current === '0') @if ($model) x-model="{{ $model }}" @endif>
            No
        </label>
    </div>
    <x-input-error :messages="$errors->get($name)" />
</fieldset>
