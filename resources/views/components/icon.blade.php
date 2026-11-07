@props(['name'])

<svg {{ $attributes->merge(['class' => 'h-5 w-5 shrink-0']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('home')
            <path d="M4 11l8-7 8 7" /><path d="M6 10v9h12v-9" /><path d="M10 19v-5h4v5" />
            @break
        @case('document')
            <path d="M7 3h7l5 5v13H7z" /><path d="M14 3v5h5" /><path d="M10 13h6M10 17h6" />
            @break
        @case('plus')
            <path d="M12 5v14M5 12h14" />
            @break
        @case('download')
            <path d="M12 4v11" /><path d="M7 11l5 5 5-5" /><path d="M5 20h14" />
            @break
        @case('user')
            <circle cx="12" cy="8" r="4" /><path d="M4 21c1-4 4-6 8-6s7 2 8 6" />
            @break
        @case('logout')
            <path d="M10 4H5v16h5" /><path d="M15 8l4 4-4 4" /><path d="M9 12h10" />
            @break
        @case('menu')
            <path d="M4 7h16M4 12h16M4 17h16" />
            @break
        @case('search')
            <circle cx="11" cy="11" r="6" /><path d="M20 20l-4.5-4.5" />
            @break
        @case('building')
            <path d="M5 21V4h9v17" /><path d="M14 10h5v11" /><path d="M8 8h3M8 12h3M8 16h3" />
            @break
        @case('card')
            <rect x="3" y="6" width="18" height="13" rx="2" /><path d="M3 10h18" />
            @break
        @case('chart')
            <path d="M5 20V10M12 20V4M19 20v-7" />
            @break
        @case('check')
            <path d="M5 12.5l4.5 4.5L19 7.5" />
            @break
        @case('eye')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" />
            @break
        @case('eye-off')
            <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12z" /><circle cx="12" cy="12" r="3" /><path d="M4 4l16 16" />
            @break
        @case('mail')
            <rect x="3" y="5" width="18" height="14" rx="2" /><path d="M3 7l9 6 9-6" />
            @break
        @case('copy')
            <rect x="9" y="9" width="11" height="11" rx="2" /><path d="M5 15V6a2 2 0 012-2h9" />
            @break
        @case('shield')
            <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6z" /><path d="M9 12l2 2 4-4" />
            @break
    @endswitch
</svg>
