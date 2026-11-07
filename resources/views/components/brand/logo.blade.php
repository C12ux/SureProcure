{{--
    Brand logo.
      on="dark"  -> placed on purple / black backgrounds
      on="light" -> placed on light backgrounds

    Files (in public/images/):
      logo.png        your logo as designed for LIGHT backgrounds   (required for the PNG to show)
      logo-light.png  a light/white version for DARK backgrounds    (optional)

    The PNG is an icon, so the "SureProcure" wordmark is written next to it.
    If your PNG ever includes the name itself, pass :wordmark="false".
    If only logo.png exists, on dark backgrounds the icon sits inside a light pill so it stays readable.
    If neither file exists, the built-in SVG mark and name are shown.
    Size: pass height="h-10" (default h-9).
--}}
@props(['on' => 'light', 'height' => 'h-9', 'wordmark' => true])

@php
    $forLight = 'images/logo.png';
    $forDark = 'images/logo-light.png';
    $hasLight = file_exists(public_path($forLight));
    $hasDark = file_exists(public_path($forDark));

    $src = $on === 'dark'
        ? ($hasDark ? $forDark : ($hasLight ? $forLight : null))
        : ($hasLight ? $forLight : ($hasDark ? $forDark : null));

    $needsPill = $on === 'dark' && ! $hasDark && $hasLight;
    $check = $on === 'dark' ? '#282391' : '#F6F7FA';
@endphp

@if ($src)
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
        <span class="inline-flex items-center {{ $needsPill ? 'rounded-xl bg-mist-50 px-3 py-1.5' : '' }}">
            {{-- The name is written beside the icon, so the image itself needs no alt text --}}
            <img src="{{ asset($src) }}" alt="{{ $wordmark ? '' : 'SureProcure' }}" class="{{ $height }} w-auto">
        </span>
        @if ($wordmark)
            <span class="font-display text-xl font-bold tracking-tight">SureProcure</span>
        @endif
    </span>
@else
    <span {{ $attributes->merge(['class' => 'inline-flex items-center gap-2.5']) }}>
        <svg viewBox="0 0 32 32" class="h-8 w-8" aria-hidden="true">
            <rect width="32" height="32" rx="9" fill="currentColor" />
            <path d="M9 16.5l4.8 4.8L23 11.5" fill="none" stroke="{{ $check }}" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
        <span class="font-display text-xl font-bold tracking-tight">SureProcure</span>
    </span>
@endif