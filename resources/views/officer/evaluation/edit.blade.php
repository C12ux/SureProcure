@php
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
@endphp

<x-app-layout>
    <x-slot name="section">Assigned requisitions</x-slot>
    <x-slot name="description">Compare quotations and prepare your recommendation.</x-slot>
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

    <form method="POST" action="{{ route('officer.evaluation.save', $requisition) }}" class="mt-6 space-y-5"
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

        <section class="app-card app-card--flush" aria-labelledby="quotation-comparison">
            <h2 id="quotation-comparison" class="app-card-title px-5 pb-4 pt-5">Quotation comparison</h2>
            <table class="data-table">
                <thead>
                    <tr><th scope="col">Vendor</th><th scope="col">Quoted amount</th><th scope="col">Delivery</th><th scope="col">Recommendation</th></tr>
                </thead>
                <tbody>
                    @foreach ($quotations as $quotation)
                        <tr>
                            <th scope="row">{{ $quotation->vendor?->name }}</th>
                            <td class="whitespace-nowrap tabular-nums">{{ $money($quotation->total_amount) }}</td>
                            <td class="whitespace-nowrap">{{ $quotation->lead_time_days !== null ? $quotation->lead_time_days.' days' : 'Not specified' }}</td>
                            <td>
                                <label class="inline-flex items-center gap-2 whitespace-nowrap">
                                    <input type="radio" name="recommended_quotation_id" value="{{ $quotation->id }}" x-model="recommended"
                                           class="field-check rounded-full" @checked((string) $recommendedId === (string) $quotation->id)
                                           aria-label="Recommend {{ $quotation->vendor?->name }}">
                                    <span x-text="recommended === '{{ $quotation->id }}' ? 'Selected' : 'Select'">Select</span>
                                </label>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>

        @foreach ($criteria as $type => $list)
            <section class="app-card app-card--flush" aria-labelledby="scores-{{ $type }}">
                <h2 id="scores-{{ $type }}" class="app-card-title px-5 pb-4 pt-5">{{ ucfirst($type) }} scores <span class="ml-1 text-xs font-medium text-ink/70">0–100</span></h2>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th scope="col">Criterion and weight</th>
                            @foreach ($quotations as $quotation)
                                <th scope="col">{{ $quotation->vendor?->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($list as $i => $criterion)
                            <tr>
                                <th scope="row" class="min-w-[12rem] whitespace-normal">
                                    {{ $criterion['name'] }}
                                    <span class="block font-normal text-ink/70">Weight {{ $criterion['weight'] }}%</span>
                                </th>
                                @foreach ($quotations as $quotation)
                                    <td>
                                        <label class="sr-only" for="s-{{ $quotation->id }}-{{ $type }}-{{ $i }}">{{ $criterion['name'] }} score for {{ $quotation->vendor?->name }}</label>
                                        <input id="s-{{ $quotation->id }}-{{ $type }}-{{ $i }}" type="number" min="0" max="100" step="0.5"
                                               name="scores[{{ $quotation->id }}][{{ $type }}][{{ $i }}]"
                                               x-model="scores[{{ $quotation->id }}].{{ $type }}[{{ $i }}]"
                                               class="field-input w-24 text-right tabular-nums" placeholder="0–100" value="{{ $initial[$quotation->id][$type][$i] }}">
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endforeach

        <section class="app-card app-card--flush" aria-labelledby="score-summary">
            <h2 id="score-summary" class="app-card-title px-5 pb-4 pt-5">Weighted score summary</h2>
            <table class="data-table">
                <thead>
                    <tr><th scope="col">Vendor</th><th scope="col">Technical</th><th scope="col">Commercial</th><th scope="col">Overall</th></tr>
                </thead>
                <tbody class="tabular-nums">
                    @foreach ($quotations as $quotation)
                        <tr>
                            <th scope="row">{{ $quotation->vendor?->name }}</th>
                            <td x-text="fmt(avg({{ $quotation->id }}, 'technical'))"></td>
                            <td x-text="fmt(avg({{ $quotation->id }}, 'commercial'))"></td>
                            <td class="font-semibold text-jackson" x-text="fmt(overall({{ $quotation->id }}))"></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </section>
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
