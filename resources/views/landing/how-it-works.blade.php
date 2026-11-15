<section id="how-it-works" class="scroll-mt-6 bg-mist-50 text-ink" aria-labelledby="how-it-works-title">
    <div class="page-container grid items-start gap-9 py-14 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20 lg:py-24">
        <div class="lg:sticky lg:top-10">
            <h2 id="how-it-works-title" class="heading-section">How it works</h2>
            <p class="lead mt-4 max-w-sm text-ink/75">From your first request to the purchase order, a dedicated officer manages sourcing and keeps you involved in the decision.</p>
        </div>

        <ol class="landing-process" role="list">
            @foreach (config('landing.steps') as $step)
                <li class="landing-reveal relative grid grid-cols-[2.5rem_1fr] gap-4 pb-7 last:pb-0 sm:gap-6">
                    <span aria-hidden="true" class="relative z-10 flex h-10 w-10 items-center justify-center rounded-full bg-jackson font-display text-sm font-bold tabular-nums text-white">
                        {{ str_pad((string) $loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </span>
                    <div class="border-b border-mist-200 pb-6 pt-1.5">
                        <h3 class="font-display text-xl font-bold tracking-tight sm:text-2xl">{{ $step['title'] }}</h3>
                        <p class="mt-2 max-w-prose leading-relaxed text-ink/75">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
