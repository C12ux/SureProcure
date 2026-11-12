{{--
    Vendor performance charts. Needs $metrics (see VendorPortal::metrics()).
    Plain HTML, CSS and SVG: no chart library, so nothing extra to load or break.
    Every chart has its numbers written out beside it.
--}}
@php
    $o = $metrics['outcomes'];
    $total = array_sum($o);
    $compact = fn ($v) => $v >= 1000000
        ? rtrim(rtrim(number_format($v / 1000000, 1), '0'), '.').'M'
        : ($v >= 1000 ? rtrim(rtrim(number_format($v / 1000, 1), '0'), '.').'K' : number_format($v));

    $max = max(1, (float) collect($metrics['months'])->max(fn ($m) => max($m['quoted'], $m['awarded'])));
    $barHeight = fn ($v) => $v > 0 ? max(4, round($v / $max * 100)) : 0;
    $hasMonthly = collect($metrics['months'])->contains(fn ($m) => $m['quoted'] > 0);

    $hours = $metrics['response_hours'];
    $response = $hours === null ? 'No quotes yet' : ($hours < 1 ? 'Under an hour' : ($hours < 48 ? round($hours).' hours' : round($hours / 24, 1).' days'));
    $lead = $metrics['avg_lead_days'];

    $segments = [
        ['key' => 'awarded', 'label' => 'Won', 'class' => 'bg-success'],
        ['key' => 'evaluating', 'label' => 'In evaluation', 'class' => 'bg-jackson'],
        ['key' => 'not_selected', 'label' => 'Not selected', 'class' => 'bg-ink/25'],
    ];

    $maxInvited = max(1, (int) (collect($metrics['categories'])->max('invited') ?? 1));
@endphp

<section class="mt-8" aria-labelledby="performance-title">
    <h2 id="performance-title" class="app-card-title">Your performance</h2>

    <div class="mt-4 grid gap-6 lg:grid-cols-3">

        {{-- Win rate --}}
        <div class="app-card">
            <h3 class="font-semibold">Win rate</h3>
            @if ($metrics['win_rate'] !== null)
                <div class="mt-4 flex items-center gap-5">
                    <svg viewBox="0 0 100 100" class="h-24 w-24 shrink-0" role="img" aria-label="Win rate {{ $metrics['win_rate'] }} percent">
                        <circle cx="50" cy="50" r="40" fill="none" stroke-width="12" class="stroke-mist-200" />
                        <circle cx="50" cy="50" r="40" fill="none" stroke-width="12" stroke-linecap="round" class="stroke-jackson"
                                stroke-dasharray="{{ round($metrics['win_rate'] * 2.513, 1) }} 251.3" transform="rotate(-90 50 50)" />
                        <text x="50" y="57" text-anchor="middle" class="fill-ink font-display text-2xl font-bold">{{ $metrics['win_rate'] }}%</text>
                    </svg>
                    <p class="text-sm text-ink/70">You won {{ $o['awarded'] }} of the {{ $metrics['decided'] }} {{ \Illuminate\Support\Str::plural('quotation', $metrics['decided']) }} customers have decided on.</p>
                </div>
            @else
                <p class="mt-3 text-sm text-ink/70">Your win rate appears once a customer has decided on one of your quotations.</p>
            @endif
        </div>

        {{-- Outcomes --}}
        <div class="app-card">
            <h3 class="font-semibold">How your quotations turned out</h3>
            @if ($total === 0)
                <p class="mt-3 text-sm text-ink/70">No quotations yet. They appear here after your procurement agent records them.</p>
            @else
                <div class="mt-4 flex h-4 overflow-hidden rounded-full bg-mist-200" role="img"
                     aria-label="{{ $o['awarded'] }} won, {{ $o['evaluating'] }} in evaluation, {{ $o['not_selected'] }} not selected">
                    @foreach ($segments as $segment)
                        @if ($o[$segment['key']] > 0)
                            <div class="{{ $segment['class'] }}" style="width: {{ $o[$segment['key']] / $total * 100 }}%" title="{{ $segment['label'] }}: {{ $o[$segment['key']] }}"></div>
                        @endif
                    @endforeach
                </div>
                <ul class="mt-4 space-y-2 text-sm">
                    @foreach ($segments as $segment)
                        <li class="flex items-center justify-between gap-3">
                            <span class="flex items-center gap-2"><span class="h-3 w-3 rounded-full {{ $segment['class'] }}"></span>{{ $segment['label'] }}</span>
                            <span class="font-semibold tabular-nums">{{ $o[$segment['key']] }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Speed and size --}}
        <div class="app-card">
            <h3 class="font-semibold">How you quote</h3>
            <dl class="mt-4 space-y-4">
                <div>
                    <dt class="detail-label">Typical time to quote</dt>
                    <dd class="detail-value">{{ $response }}</dd>
                </div>
                <div>
                    <dt class="detail-label">Typical delivery you promise</dt>
                    <dd class="detail-value">{{ $lead !== null ? round($lead).' days' : 'Not enough quotes yet' }}</dd>
                </div>
                <div>
                    <dt class="detail-label">Average quotation</dt>
                    <dd class="detail-value tabular-nums">{{ $metrics['avg_quote'] !== null ? 'PHP '.number_format($metrics['avg_quote'], 2) : 'No quotes yet' }}</dd>
                </div>
            </dl>
        </div>

        {{-- Value by month --}}
        <div class="app-card lg:col-span-2">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <h3 class="font-semibold">Quotation value by month</h3>
                <div class="flex items-center gap-4 text-xs text-ink/70">
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-jackson/40"></span>Quoted</span>
                    <span class="flex items-center gap-1.5"><span class="h-3 w-3 rounded-sm bg-jackson"></span>Won</span>
                </div>
            </div>

            @if (! $hasMonthly)
                <p class="mt-3 text-sm text-ink/70">The last six months fill in as quotations are recorded.</p>
            @else
                <div class="mt-5 flex items-end gap-3" role="img"
                     aria-label="Quoted and won value for the last six months. {{ collect($metrics['months'])->map(fn ($m) => $m['label'].': quoted '.$compact($m['quoted']).', won '.$compact($m['awarded']))->join('; ') }}">
                    @foreach ($metrics['months'] as $month)
                        <div class="flex flex-1 flex-col items-center gap-2">
                            <p class="text-xs font-semibold tabular-nums text-ink/70">{{ $month['quoted'] > 0 ? $compact($month['quoted']) : '' }}&nbsp;</p>
                            <div class="flex h-40 w-full items-end justify-center gap-1">
                                <div class="w-1/2 max-w-[1.75rem] rounded-t bg-jackson/40" style="height: {{ $barHeight($month['quoted']) }}%" title="{{ $month['label'] }}: quoted PHP {{ number_format($month['quoted'], 2) }}"></div>
                                <div class="w-1/2 max-w-[1.75rem] rounded-t bg-jackson" style="height: {{ $barHeight($month['awarded']) }}%" title="{{ $month['label'] }}: won PHP {{ number_format($month['awarded'], 2) }}"></div>
                            </div>
                            <span class="text-xs text-ink/60">{{ $month['label'] }}</span>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-xs text-ink/60">Values in PHP, grouped by the date each quotation was recorded.</p>
            @endif
        </div>

        {{-- Orders --}}
        <div class="app-card">
            <h3 class="font-semibold">Your orders</h3>
            @if ($metrics['orders']['open'] + $metrics['orders']['completed'] === 0)
                <p class="mt-3 text-sm text-ink/70">Orders you win show up here, split into open and completed.</p>
            @else
                <dl class="mt-4 space-y-4">
                    @if ($metrics['orders']['new'] > 0)
                        <div>
                            <dt class="detail-label">New, waiting for you to acknowledge</dt>
                            <dd class="detail-value">{{ $metrics['orders']['new'] }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="detail-label">Open orders</dt>
                        <dd class="detail-value tabular-nums">{{ $metrics['orders']['open'] }}, PHP {{ number_format($metrics['orders']['open_value'], 2) }}</dd>
                    </div>
                    <div>
                        <dt class="detail-label">Completed orders</dt>
                        <dd class="detail-value tabular-nums">{{ $metrics['orders']['completed'] }}, PHP {{ number_format($metrics['orders']['completed_value'], 2) }}</dd>
                    </div>
                </dl>
            @endif
        </div>

        {{-- Categories --}}
        <div class="app-card lg:col-span-3">
            <h3 class="font-semibold">Where your invitations come from</h3>
            @if (empty($metrics['categories']))
                <p class="mt-3 text-sm text-ink/70">Once you are invited to requests, the categories they come from are charted here.</p>
            @else
                <ul class="mt-4 space-y-4">
                    @foreach ($metrics['categories'] as $category)
                        <li>
                            <div class="flex items-baseline justify-between gap-3 text-sm">
                                <span class="font-medium">{{ $category['name'] }}</span>
                                <span class="tabular-nums text-ink/70">{{ $category['invited'] }} invited, {{ $category['quoted'] }} quoted</span>
                            </div>
                            <div class="mt-1.5 h-2 overflow-hidden rounded-full bg-mist-200">
                                <div class="h-full rounded-full bg-jackson/40" style="width: {{ $category['invited'] / $maxInvited * 100 }}%">
                                    <div class="h-full rounded-full bg-jackson" style="width: {{ $category['invited'] > 0 ? $category['quoted'] / $category['invited'] * 100 : 0 }}%"></div>
                                </div>
                            </div>
                        </li>
                    @endforeach
                </ul>
                <p class="mt-4 text-xs text-ink/60">The darker part of each bar is the share of invitations you answered with a quotation.</p>
            @endif
        </div>
    </div>
</section>
