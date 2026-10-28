<header class="page-container flex items-center justify-between py-6">
    <a href="{{ url('/') }}" class="text-mist-50" aria-label="SureProcure home">
        <x-brand.logo on="dark" />
    </a>

    <nav class="hidden items-center gap-9 text-sm font-medium md:flex" aria-label="Main">
        <a href="#how-it-works" class="nav-link">How it works</a>
        <a href="#for-customers" class="nav-link">Customers</a>
        <a href="#vendors" class="nav-link">Vendors</a>
        <a href="#officers" class="nav-link">Procurement officers</a>
    </nav>

    <div class="flex items-center gap-2 sm:gap-3">
        @auth
            <a href="{{ Route::has('dashboard') ? route('dashboard') : url('/') }}" class="btn btn-sm btn-light">Go to dashboard</a>
        @else
            @if (Route::has('login'))
                <a href="{{ route('login') }}" class="link-quiet">Log in</a>
            @endif
            @if (Route::has('register'))
                <a href="{{ route('register') }}" class="btn btn-sm btn-light">Create account</a>
            @endif
        @endauth
    </div>
</header>
