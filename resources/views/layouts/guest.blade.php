<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SureProcure') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:500,600,700,800|figtree:400,500,600,700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing bg-mist-50 font-sans text-ink antialiased">
    <div class="grid min-h-screen lg:grid-cols-12">

        {{-- Brand panel (desktop only) --}}
        <aside class="hidden bg-jackson text-mist lg:col-span-5 lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
            <a href="{{ url('/') }}" class="text-mist-50" aria-label="SureProcure home"><x-brand.logo on="dark" /></a>

            <div>
                <p class="heading-section text-mist-50">One place for every request, bid and approval.</p>
                <ul class="mt-9 space-y-3.5 text-base">
                    @foreach (['A dedicated procurement officer on every request', 'Only accredited vendors invited to quote', 'One bid evaluation to review and approve'] as $point)
                        <li class="flex gap-3"><x-brand.check class="audience-check" />{{ $point }}</li>
                    @endforeach
                </ul>
            </div>

            <p class="text-sm text-mist/80">&copy; {{ date('Y') }} SureProcure</p>
        </aside>

        {{-- Form panel --}}
        <main class="flex items-center justify-center px-6 py-12 sm:py-16 lg:col-span-7">
            <div class="w-full max-w-md">
                <a href="{{ url('/') }}" class="mb-10 inline-block text-ink lg:hidden" aria-label="SureProcure home"><x-brand.logo on="light" /></a>
                {{ $slot }}
            </div>
        </main>
    </div>
</body>
</html>
