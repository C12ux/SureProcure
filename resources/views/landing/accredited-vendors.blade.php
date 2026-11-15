<section aria-labelledby="accredited-vendors-title" class="landing-enter landing-enter-later min-w-0 rounded-3xl border border-white/35 bg-mist-50 px-5 py-6 text-ink shadow-2xl shadow-ink/25 sm:p-8">
    <h2 id="accredited-vendors-title" class="font-display text-2xl font-extrabold tracking-tight sm:text-3xl">Accredited vendors</h2>
    <p class="mt-2 text-sm text-ink/70">Companies eligible to be invited to quote through SureProcure.</p>
    @if ($vendors === null)
        <p class="mt-5 border-t border-mist-200 pt-5 text-sm text-ink/70">Vendor listings are temporarily unavailable.</p>
    @else
        <ul class="mt-5 divide-y divide-mist-200 border-y border-mist-200" role="list">
            @forelse ($vendors as $vendor)
                <li class="landing-vendor-row flex min-w-0 items-center gap-3 py-3.5">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-jackson/10 text-jackson" aria-hidden="true">
                        <svg viewBox="0 0 20 20" class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="m4 10 4 4 8-8" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </span>
                    <span class="min-w-0 break-words font-semibold [overflow-wrap:anywhere]">{{ $vendor->display_name }}</span>
                </li>
            @empty
                <li class="py-5 text-sm text-ink/70">There are no accredited vendors to display yet.</li>
            @endforelse
        </ul>
    @endif
</section>
