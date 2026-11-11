@php
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
@endphp

<x-app-layout>
    <x-slot name="title">Bid evaluation</x-slot>
    <x-slot name="actions">
        <a href="{{ route('officer.requisitions.show', $requisition) }}" class="btn btn-sm btn-outline-dark">Back to {{ $requisition->pr_number }}</a>
    </x-slot>

    <p class="lead max-w-3xl text-ink/70">
        {{ $requisition->title }}. Score each vendor from 0 to 100 on every criterion. The weight shows how much each criterion counts.
        Technical and commercial scores count equally toward the overall score.
    </p>

    @if ($errors->has('evaluation'))
        <x-alert type="warning" class="mt-6">
            <span class="font-semibold">Saved as a draft, but it is not ready to send yet:</span>
            <ul class="mt-1 list-inside list-disc font-normal">
                @foreach ($errors->get('evaluation') as $messages)
                    @foreach ((array) $messages as $message)
                        <li>{{ $message }}</li>
                    @endforeach
                @endforeach
            </ul>
        </x-alert>
    @endif

    <form method="POST" action="{{ route('officer.evaluation.save', $requisition) }}" class="mt-6 max-w-4xl space-y-6"
          x-data="{
              scores: @js($initial),
              weights: @js($weights),
              recommended: '{{ $recommendedId }}',
              avg(id, type) {
                  let w = 0, t = 0;
                  this.weights[type].forEach((weight, i) => {
                      const v = parseFloat(this.scores[id][type][i]);
                      if (!isNaN(v)) { w += weight; t += weight * v; }
                  });
                  return w ? t / w : null;
              },
              overall(id) {
                  let w = 0, t = 0;
                  Object.keys(this.weights).forEach((type) => {
                      this.weights[type].forEach((weight, i) => {
                          const v = parseFloat(this.scores[id][type][i]);
                          if (!isNaN(v)) { w += weight; t += weight * v; }
                      });
                  });
                  return w ? t / w : null;
              },
              fmt(v) { return v === null ? 'Not scored' : v.toFixed(1); }
          }">
        @csrf

        @foreach ($quotations as $quotation)
            <section class="app-card">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <h2 class="app-card-title">{{ $quotation->vendor?->name }}</h2>
                        <p class="mt-1 text-sm text-ink/70">
                            Quoted {{ $money($quotation->total_amount) }}@if ($quotation->lead_time_days !== null), delivery in {{ $quotation->lead_time_days }} days @endif
                        </p>
                    </div>
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-ink/15 px-4 py-2 text-sm font-semibold"
                           x-bind:class="recommended === '{{ $quotation->id }}' ? 'border-jackson bg-jackson/5 text-jackson' : ''">
                        <input type="radio" name="recommended_quotation_id" value="{{ $quotation->id }}" x-model="recommended" class="field-check rounded-full">
                        Recommend this vendor
                    </label>
                </div>

                @foreach ($criteria as $type => $list)
                    <div class="mt-6">
                        <h3 class="font-semibold">{{ ucfirst($type) }}</h3>
                        <div class="mt-3 divide-y divide-ink/10">
                            @foreach ($list as $i => $criterion)
                                <div class="flex items-center justify-between gap-4 py-3">
                                    <label for="s-{{ $quotation->id }}-{{ $type }}-{{ $i }}" class="min-w-0 text-sm">
                                        {{ $criterion['name'] }}
                                        <span class="text-ink/60">(weight {{ $criterion['weight'] }})</span>
                                    </label>
                                    <input id="s-{{ $quotation->id }}-{{ $type }}-{{ $i }}" type="number" min="0" max="100" step="0.5"
                                           name="scores[{{ $quotation->id }}][{{ $type }}][{{ $i }}]"
                                           x-model="scores[{{ $quotation->id }}].{{ $type }}[{{ $i }}]"
                                           class="field-input w-28 shrink-0 text-right tabular-nums" placeholder="0 to 100">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                <div class="mt-5 flex flex-wrap items-baseline justify-between gap-3 border-t border-ink/10 pt-4">
                    <p class="text-sm text-ink/70">
                        Technical <span class="font-semibold tabular-nums text-ink" x-text="fmt(avg({{ $quotation->id }}, 'technical'))"></span>,
                        commercial <span class="font-semibold tabular-nums text-ink" x-text="fmt(avg({{ $quotation->id }}, 'commercial'))"></span>
                    </p>
                    <p class="font-display text-xl font-bold">Overall <span class="tabular-nums" x-text="fmt(overall({{ $quotation->id }}))"></span></p>
                </div>
            </section>
        @endforeach
        <x-input-error :messages="$errors->get('recommended_quotation_id')" />
        @foreach ($errors->get('scores.*') as $messages)
            <x-input-error :messages="$messages" />
        @endforeach

        <section class="app-card">
            <h2 class="app-card-title">Recommendation</h2>
            <p class="field-hint">Explain your choice in plain words. The customer reads this before approving.</p>
            <textarea id="recommendation" name="recommendation" rows="4" class="field-input mt-3" aria-label="Recommendation">{{ $recommendation }}</textarea>
            <x-input-error :messages="$errors->get('recommendation')" />
        </section>

        <div class="flex flex-wrap items-center gap-3">
            <button type="submit" name="action" value="draft" class="btn btn-lg btn-outline-dark">Save draft</button>
            <button type="submit" name="action" value="submit" class="btn btn-lg btn-solid">Send to customer for approval</button>
        </div>
    </form>
</x-app-layout>
