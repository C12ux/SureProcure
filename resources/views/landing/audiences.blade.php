<section class="landing-audiences workspace-rail text-white" aria-labelledby="audiences-title">
    <div class="page-container py-14 lg:py-24">
        <h2 id="audiences-title" class="heading-section">Who it’s for</h2>

        <div class="mt-7 border-t border-white/20 lg:mt-10">
            @foreach (config('landing.audiences') as $audience)
                <article id="{{ $audience['id'] }}" class="landing-audience-row grid scroll-mt-6 items-baseline gap-3 border-b border-white/20 py-7 md:grid-cols-[2fr_3fr] md:gap-12 lg:py-9">
                    <h3 class="font-display text-2xl font-extrabold tracking-tight lg:text-3xl">{{ $audience['title'] }}</h3>
                    <p class="max-w-xl text-base leading-relaxed sm:text-lg">{{ $audience['description'] }}</p>
                </article>
            @endforeach
        </div>
    </div>
</section>
