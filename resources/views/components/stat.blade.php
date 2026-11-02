@props(['label', 'value', 'tone' => 'neutral'])

<div {{ $attributes->merge(['class' => 'stat '.($tone === 'attention' ? 'stat--attention' : '')]) }}>
    <p class="stat-value">{{ $value }}</p>
    <p class="stat-label">{{ $label }}</p>
</div>
