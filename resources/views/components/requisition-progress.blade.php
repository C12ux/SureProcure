@props(['status'])

@php
    $steps = ['Submitted', 'In review', 'Sourcing', 'Bid evaluation', 'Your approval', 'PO issued', 'Closed'];
    $current = $status->progressIndex();
@endphp

@if ($current !== null)
    <ol class="progress" aria-label="Request progress">
        @foreach ($steps as $i => $label)
            @php $state = $i < $current ? 'done' : ($i === $current ? 'current' : 'todo'); @endphp
            <li class="progress-step {{ $state === 'done' ? 'progress-step--done' : ($state === 'current' ? 'progress-step--current' : '') }}"
                @if ($state === 'current') aria-current="step" @endif>
                @if ($state === 'done')
                    <x-brand.check class="h-3.5 w-3.5 text-jackson" />
                @endif
                {{ $label }}
            </li>
        @endforeach
    </ol>
@endif
