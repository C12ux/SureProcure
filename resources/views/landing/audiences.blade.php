<section class="bg-mist-50" aria-labelledby="audience-heading">
    <div class="page-container pb-14 lg:pb-20">
        <h2 id="audience-heading" class="heading-section text-ink">Who it’s for</h2>
        <div class="mt-7 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach (config('landing.audiences') as $audience)
                <article id="{{ $audience['id'] }}" class="app-card flex scroll-mt-4 flex-col">
                    <h3 class="app-card-title">{{ $audience['title'] }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-ink/75">{{ $audience['intro'] }}</p>
                    <ul class="mt-4 space-y-2 text-sm">
                        @foreach ($audience['items'] as $item)
                            <li class="flex gap-2">
                                <x-brand.check class="mt-0.5 h-4 w-4 shrink-0 text-jackson" />
                                {{ $item }}
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-4 mt-4 text-sm leading-relaxed text-ink/75">{{ $audience['note'] }}</p>
                    @if (Route::has($audience['cta']['route']))
                        <a href="{{ route($audience['cta']['route']) }}" class="btn btn-sm btn-outline-dark mt-auto self-start">
                            {{ $audience['cta']['label'] }}
                        </a>
                    @endif
                </article>
            @endforeach
        </div>
    </div>
</section>
