<section class="bg-ink text-mist">
    <div class="page-container grid divide-y divide-mist/15 lg:grid-cols-3 lg:divide-x lg:divide-y-0">
        @foreach (config('landing.audiences') as $audience)
            <div id="{{ $audience['id'] }}" class="audience-col scroll-mt-4">
                <h2 class="heading-column">{{ $audience['title'] }}</h2>
                <p class="lead mt-3">{{ $audience['intro'] }}</p>

                <ul class="audience-list">
                    @foreach ($audience['items'] as $item)
                        <li class="flex gap-3">
                            <x-brand.check class="audience-check {{ $audience['accent'] === 'success' ? 'audience-check--success' : '' }}" />
                            {{ $item }}
                        </li>
                    @endforeach
                </ul>

                <p class="audience-note">{{ $audience['note'] }}</p>

                @if (Route::has($audience['cta']['route']))
                    <a href="{{ route($audience['cta']['route']) }}"
                       class="btn btn-md mt-6 {{ $audience['cta']['style'] === 'light' ? 'btn-light' : 'btn-outline-light' }}">
                        {{ $audience['cta']['label'] }}
                    </a>
                @endif
            </div>
        @endforeach
    </div>
</section>
