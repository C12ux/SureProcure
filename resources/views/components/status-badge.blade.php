@props(['status', 'staff' => false])

<span {{ $attributes->merge(['class' => 'badge badge--'.$status->tone()]) }}>
    <span class="badge-dot"></span>{{ $staff ? $status->staffLabel() : $status->label() }}
</span>
