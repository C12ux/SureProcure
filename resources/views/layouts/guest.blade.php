<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'SureProcure') }}</title>
    <title>Sure Procure</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=bricolage-grotesque:500,600,700,800|figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="marketing bg-mist-50 font-sans antialiased">
    <div class="min-h-screen lg:grid lg:grid-cols-[minmax(20rem,37%)_1fr]">
        <aside class="workspace-rail hidden flex-col justify-between gap-12 p-10 text-white lg:flex xl:p-14">
            <a href="{{ url('/') }}" aria-label="SureProcure home"><x-brand.logo on="dark" /></a>
            <div>
                <p class="font-display text-4xl font-extrabold leading-tight tracking-tight xl:text-5xl">Procurement with confidence.</p>
                <p class="mt-4 max-w-sm leading-relaxed text-white/80">Manage purchase requests, sourcing, and accreditation in one workspace.</p>
            </div>
            <p class="text-sm text-white/70">&copy; {{ date('Y') }} SureProcure</p>
        </aside>

        <main class="flex items-center justify-center px-4 py-8 sm:px-8 sm:py-14">
            <div class="w-full max-w-[33rem]">
                <a href="{{ url('/') }}" class="mb-6 inline-block lg:hidden" aria-label="SureProcure home"><x-brand.logo on="light" /></a>
                <div class="app-card guest-card">{{ $slot }}</div>
            </div>
        </main>
    </div>
</body>
</html>
