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
<body class="app bg-mist-50 font-sans antialiased" x-data="{ nav: false }"
      x-on:keydown.escape.window="if (nav) { nav = false; $nextTick(() => $refs.menu.focus()); }"
      x-on:resize.window="if (window.innerWidth >= 1024) nav = false">
    @php
        $account = auth()->user();
        $roleLabel = match ($account->role?->value) {
            'client' => 'Customer',
            'vendor' => 'Vendor',
            'procurement_officer' => 'Procurement officer',
            'admin' => 'Admin',
            default => 'Account',
        };
        $workspaceName = $account->organizations->first()?->display_name ?? $roleLabel;
    @endphp

    <a href="#main-content" class="skip-link">Skip to content</a>
    <div x-cloak x-show="nav" x-on:click="nav = false; $refs.menu.focus()" class="fixed inset-0 z-40 bg-ink/60 lg:hidden" aria-hidden="true"></div>

    <aside id="workspace-navigation" class="workspace-rail fixed inset-y-0 left-0 z-50 w-[220px] overflow-y-auto lg:visible lg:translate-x-0"
           x-bind:class="nav ? 'visible translate-x-0' : 'invisible -translate-x-full'">
        @include('layouts.sidebar', ['user' => $account])
    </aside>

    <div class="min-h-screen lg:pl-[220px]" x-bind:inert="nav">
        <div class="workspace-context sticky top-0 z-30">
            <div class="workspace-width flex min-h-[3.875rem] items-center justify-between gap-4 px-4 sm:px-7">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" class="rounded-lg p-2 text-jackson lg:hidden" x-ref="menu"
                            x-bind:aria-expanded="nav.toString()" aria-controls="workspace-navigation"
                            x-on:click="nav = true; $nextTick(() => $refs.closeMenu.focus())" aria-label="Open workspace navigation">
                        <x-icon name="menu" />
                    </button>
                    <span class="truncate text-sm font-semibold">{{ $roleLabel }} <span class="mx-1 text-ink/45" aria-hidden="true">/</span> {{ $section ?? 'Workspace' }}</span>
                </div>
                <a href="{{ route('profile.edit') }}" class="hidden truncate text-sm font-semibold text-jackson sm:block">{{ $workspaceName }}</a>
            </div>
        </div>

        <main id="main-content" tabindex="-1" class="workspace-width px-4 pb-10 pt-6 sm:px-7">
            @if (isset($title) || isset($header))
                <header class="mb-5 flex flex-wrap items-center justify-between gap-4">
                    <div class="min-w-0">
                        <h1 class="page-title">{{ $title ?? $header }}</h1>
                        @isset($description)
                            <div class="mt-1.5 text-sm leading-relaxed text-ink/70">{{ $description }}</div>
                        @endisset
                    </div>
                    @isset($actions)
                        <div class="flex flex-wrap items-center gap-2">{{ $actions }}</div>
                    @endisset
                </header>
            @endif

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
