@props(['title', 'text' => null])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <p class="font-display text-xl font-bold tracking-tight">{{ $title }}</p>
    @if ($text)
        <p class="mt-2 max-w-sm text-ink/70">{{ $text }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-6">{{ $slot }}</div>
    @endif
</div>
