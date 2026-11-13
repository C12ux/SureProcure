<section id="how-it-works" class="scroll-mt-4 bg-mist-50">
    <div class="page-container py-14 lg:py-20">
        <h2 class="heading-section text-ink">How it works</h2>
        <ol class="mt-7 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach (config('landing.steps') as $step)
                <li class="app-card">
                    <h3 class="app-card-title">{{ $step['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $step['text'] }}</p>
                </li>
            @endforeach
        </ol>
    </div>
</section>
