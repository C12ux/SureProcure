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
<body class="app bg-mist-50 font-sans text-ink antialiased" x-data="{ nav: false }" x-on:keydown.escape.window="nav = false">

    {{-- Dim the page behind the sidebar on phones --}}
    <div x-cloak x-show="nav" x-on:click="nav = false" class="fixed inset-0 z-40 bg-ink/60 lg:hidden"></div>

    <aside class="fixed inset-y-0 left-0 z-50 w-72 transform bg-jackson transition-transform lg:translate-x-0"
           x-bind:class="nav ? 'translate-x-0' : '-translate-x-full'">
        @include('layouts.sidebar')
    </aside>

    <div class="lg:pl-72">
        <header class="sticky top-0 z-30 flex items-center gap-4 border-b border-ink/10 bg-mist-50/90 px-4 py-4 backdrop-blur sm:px-8">
            <button type="button" class="-ml-1 rounded-lg p-2 lg:hidden" x-on:click="nav = true" aria-label="Open menu">
                <x-icon name="menu" />
            </button>

            @if (isset($title) || isset($header))
                <h1 class="page-title">{{ $title ?? $header }}</h1>
            @endif

            <div class="ml-auto flex items-center gap-3">{{ $actions ?? '' }}</div>
        </header>

        <main class="px-4 py-8 sm:px-8">
            @if (session('success'))
                <x-alert type="success">{{ session('success') }}</x-alert>
            @endif
            @if (session('warning'))
                <x-alert type="warning">{{ session('warning') }}</x-alert>
            @endif

            {{ $slot }}
        </main>
    </div>
</body>
</html>
