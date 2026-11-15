@php($contactEmail = filter_var(config('landing.contact_email'), FILTER_VALIDATE_EMAIL))

<footer class="workspace-rail border-t border-white/15 text-mist-50">
    <div class="page-container flex flex-wrap items-start justify-between gap-6 py-8 sm:items-center">
        <a href="{{ route('home') }}" aria-label="SureProcure home"><x-brand.logo on="dark" /></a>
        <p class="text-sm text-white/75">&copy; {{ date('Y') }} SureProcure. All rights reserved.</p>
        <div class="flex flex-wrap gap-x-5 gap-y-2 text-sm">
            @if ($contactEmail)
                <a href="mailto:{{ str_replace('%40', '@', rawurlencode($contactEmail)) }}" class="underline underline-offset-4 hover:text-white">Email SureProcure</a>
            @endif
        </div>
    </div>
</footer>
