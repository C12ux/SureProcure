<header class="border-b border-white/15">
    <div class="page-container flex flex-wrap items-center justify-between gap-x-6 gap-y-4 py-4 lg:py-5">
        <a href="{{ route('home') }}" class="text-mist-50" aria-label="SureProcure home">
            <x-brand.logo on="dark" />
        </a>

        <nav class="order-last flex w-full flex-wrap items-center gap-x-5 gap-y-2 text-sm font-medium lg:order-none lg:w-auto" aria-label="Main">
            <a href="#how-it-works" class="nav-link">How it works</a>
            <a href="#for-customers" class="nav-link">Customers</a>
            <a href="#vendors" class="nav-link">Vendors</a>
            <a href="#officers" class="nav-link">Procurement officers</a>
        </nav>

        <div class="flex flex-wrap items-center gap-x-4 gap-y-2">
            @auth
                <a href="{{ route('dashboard') }}" class="nav-link text-sm font-semibold">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="nav-link text-sm font-semibold">Log in</a>
            @endauth
            <a href="#vendor-inquiry" class="btn btn-sm btn-light">Want to be a Vendor?</a>
        </div>
    </div>
</header>
