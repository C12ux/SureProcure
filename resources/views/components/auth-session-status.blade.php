@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'rounded-xl bg-success/25 px-4 py-3 text-sm font-medium text-ink']) }}>
        {{ $status }}
    </div>
@endif
