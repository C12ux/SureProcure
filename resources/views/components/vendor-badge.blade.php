@props(['vendor'])

<span {{ $attributes->merge(['class' => 'badge badge--'.$vendor->statusTone()]) }}>
    <span class="badge-dot"></span>{{ $vendor->statusLabel() }}
</span>
