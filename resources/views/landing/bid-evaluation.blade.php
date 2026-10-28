{{-- The product's actual deliverable, shown as a sample. Data: config/landing.php --}}
<figure class="eval-card" aria-label="Sample bid evaluation">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="font-display text-xl font-bold tracking-tight">Bid evaluation</p>
            <p class="mt-1 text-sm text-ink/70">Office furniture, 40 workstations</p>
        </div>
        <span class="chip-status">
            <span class="h-2 w-2 rounded-full bg-caution"></span>
            Awaiting your approval
        </span>
    </div>

    <ul class="mt-7 divide-y divide-ink/10">
        @foreach (config('landing.bids') as $bid)
            <li class="py-4">
                <div class="flex items-baseline justify-between gap-3">
                    <p class="flex items-center gap-2 font-semibold">
                        {{ $bid['name'] }}
                        @if ($bid['recommended'])
                            <span class="chip-recommended"><x-brand.check class="h-3 w-3" /> Recommended</span>
                        @endif
                    </p>
                    <p class="font-display text-2xl font-bold tabular-nums">{{ $bid['total'] }}</p>
                </div>
                <div class="bar-track">
                    <div class="bar-fill {{ $bid['recommended'] ? 'bar-fill--best' : '' }}"
                         style="width: {{ $bid['total'] }}%; --d: {{ 350 + $loop->index * 150 }}ms;"></div>
                </div>
                <p class="mt-2 text-xs text-ink/70">Technical {{ $bid['technical'] }}, commercial {{ $bid['commercial'] }}</p>
            </li>
        @endforeach
    </ul>

    <figcaption class="mt-3 border-t border-ink/10 pt-5 text-sm leading-relaxed text-ink/80">
        Vendor A has the strongest combined score and the shortest delivery time. Prepared by your procurement officer.
    </figcaption>
</figure>
