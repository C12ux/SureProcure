<section id="how-it-works" class="scroll-mt-4 bg-mist-50">
    <div class="page-container grid gap-12 py-24 lg:grid-cols-12 lg:py-32">
        <div class="lg:col-span-4">
            <div class="lg:sticky lg:top-12">
                <h2 class="heading-section">From request to purchase order</h2>
                <p class="lead mt-5 max-w-sm text-ink/75">You decide at the points that matter. We do the legwork in between.</p>
            </div>
        </div>

        <ol class="lg:col-span-8">
            @foreach (config('landing.steps') as $step)
                <li class="step-row">
                    <span class="step-number">{{ $loop->iteration }}</span>
                    <div>
                        <h3 class="heading-step">{{ $step['title'] }}</h3>
                        <p class="mt-2 max-w-xl text-base leading-relaxed text-ink/75">{{ $step['text'] }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>
