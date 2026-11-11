{{-- Read-only bid evaluation. Needs $requisition (with quotations.vendor.organization) and $evaluation. --}}
@php
    $totals = $evaluation->totalsByQuotation();
    $byId = $requisition->quotations->keyBy('id');
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
    $fmt = fn ($value) => $value === null ? 'Not scored' : number_format($value, 1);
@endphp

<section class="app-card">
    <h3 class="app-card-title">Bid evaluation</h3>
    <p class="mt-1 text-sm text-ink/70">Each vendor is scored from 0 to 100 on technical and commercial criteria. The overall score weighs both equally.</p>

    @if ($totals->isEmpty())
        <p class="mt-4 text-sm text-ink/70">No scores have been entered yet.</p>
    @else
        <ul class="mt-5 divide-y divide-ink/10">
            @foreach ($totals as $quotationId => $score)
                @php $quotation = $byId->get($quotationId); @endphp
                @continue(! $quotation)
                <li class="py-4">
                    <div class="flex flex-wrap items-baseline justify-between gap-3">
                        <p class="flex flex-wrap items-center gap-2 font-semibold">
                            {{ $quotation->vendor?->name }}
                            @if ((int) $evaluation->recommended_quotation_id === (int) $quotationId)
                                <span class="chip-recommended"><x-brand.check class="h-3 w-3" /> Recommended</span>
                            @endif
                        </p>
                        <p class="font-display text-2xl font-bold tabular-nums">{{ $fmt($score['overall']) }}</p>
                    </div>
                    <div class="bar-track">
                        <div class="bar-fill {{ (int) $evaluation->recommended_quotation_id === (int) $quotationId ? 'bar-fill--best' : '' }}"
                             style="width: {{ min(100, max(0, (float) ($score['overall'] ?? 0))) }}%; --d: 200ms;"></div>
                    </div>
                    <p class="mt-2 text-sm text-ink/70">
                        Technical {{ $fmt($score['technical']) }}, commercial {{ $fmt($score['commercial']) }}.
                        Quoted {{ $money($quotation->total_amount) }}@if ($quotation->lead_time_days !== null), delivery in {{ $quotation->lead_time_days }} days @endif
                    </p>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($evaluation->recommendation)
        <div class="mt-4 border-t border-ink/10 pt-4">
            <p class="detail-label">Recommendation</p>
            <p class="mt-1 whitespace-pre-line">{{ $evaluation->recommendation }}</p>
        </div>
    @endif
</section>
