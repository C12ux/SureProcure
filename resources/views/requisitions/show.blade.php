@use('App\Enums\PrStatus')

@php
    $status = $requisition->status;
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
    $estimatedTotal = $requisition->items->sum(fn ($item) => $item->line_total);
@endphp

<x-app-layout>
    <x-slot name="section">Requisitions</x-slot>
    <x-slot name="description"><span class="font-mono">{{ $requisition->pr_number }}</span></x-slot>
    <x-slot name="title">{{ $requisition->title }}</x-slot>
    <x-slot name="actions">
        @if ($can['update'])
            <a href="{{ route('requisitions.edit', $requisition) }}" class="btn btn-sm btn-outline-dark">Edit</a>
        @endif
        @if ($can['submit'])
            <form method="POST" action="{{ route('requisitions.submit', $requisition) }}">
                @csrf
                <button type="submit" class="btn btn-sm btn-solid">Submit request</button>
            </form>
        @endif
    </x-slot>

    <div class="flex flex-wrap items-center gap-3">
        <x-status-badge :status="$status" />
    </div>

    {{-- Where is it? --}}
    <div class="mt-6">
        @if ($status === PrStatus::Draft)
            <x-alert type="info">This is a draft. Submit it when you are ready and your procurement officer will start working on it.</x-alert>
        @elseif ($status === PrStatus::Rejected)
            <x-alert type="error">This request was rejected. See the activity on the right for details.</x-alert>
        @elseif ($status === PrStatus::Cancelled)
            <x-alert type="warning">This request was cancelled.</x-alert>
        @else
            <div class="app-card">
                <x-requisition-progress :status="$status" />
            </div>
            @if ($status === PrStatus::NeedsClarification)
                <x-alert type="warning" class="mt-4 mb-0">Your officer asked for more information. Read their latest note in the activity on the right, then reply with a note below.</x-alert>
            @elseif ($status === PrStatus::AwaitingClientReview && $can['decide'])
                <x-alert type="warning" class="mt-4 mb-0">Your officer finished evaluating the bids. Review the recommendation below and approve it, ask for changes, or reject it.</x-alert>
            @elseif ($status === PrStatus::AwaitingClientReview)
                <x-alert type="info" class="mt-4 mb-0">The bid evaluation is waiting for an owner or approver at your company to review it.</x-alert>
            @endif
        @endif
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            {{-- Bid evaluation from the officer, with the customer's decision --}}
            @if ($evaluation)
                @include('requisitions._evaluation')

                @if ($can['decide'])
                    <section class="app-card">
                        <h3 class="app-card-title">Your decision</h3>
                        <form method="POST" action="{{ route('requisitions.decide', $requisition) }}" class="mt-5 space-y-4"
                              x-data="{ action: '{{ old('action', '') }}' }">
                            @csrf
                            <fieldset>
                                <legend class="sr-only">Choose a decision</legend>
                                <div class="space-y-2">
                                    <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="approve" x-model="action" class="field-check rounded-full" required> Approve the recommendation</label>
                                    <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="changes" x-model="action" class="field-check rounded-full"> Ask my officer for changes</label>
                                    <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="reject" x-model="action" class="field-check rounded-full"> Reject</label>
                                </div>
                                <x-input-error :messages="$errors->get('action')" />
                            </fieldset>

                            <div x-show="action !== ''" x-cloak>
                                <x-input-label for="note" value="Note" />
                                <textarea id="note" name="note" rows="3" class="field-input mt-1.5">{{ old('note') }}</textarea>
                                <p class="field-hint" x-show="action === 'changes' || action === 'reject'">Your officer sees this note, so be specific.</p>
                                <x-input-error :messages="$errors->get('note')" />
                            </div>

                            <button type="submit" class="btn btn-md btn-solid" x-show="action !== ''" x-cloak>Confirm decision</button>
                        </form>
                    </section>
                @elseif ($evaluation->decision_remarks && in_array($evaluation->status->value, ['approved', 'rejected'], true))
                    <section class="app-card">
                        <h3 class="app-card-title">Decision</h3>
                        <p class="mt-3">{{ $evaluation->decision_remarks }}</p>
                    </section>
                @endif
            @endif

            @include('requisitions._purchase-order')

            <section class="app-card">
                <h3 class="app-card-title">Details</h3>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><dt class="detail-label">Category</dt><dd class="detail-value">{{ $requisition->category?->name ?? 'Not set' }}</dd></div>
                    <div><dt class="detail-label">Priority</dt><dd class="detail-value">{{ $requisition->priority->label() }}</dd></div>
                    <div><dt class="detail-label">Needed by</dt><dd class="detail-value">{{ $requisition->needed_by_date?->format('F j, Y') ?? 'Not set' }}</dd></div>
                    <div><dt class="detail-label">Budget</dt><dd class="detail-value tabular-nums">{{ $requisition->budget !== null ? $money($requisition->budget) : 'Not set' }}</dd></div>
                    <div><dt class="detail-label">Requested by</dt><dd class="detail-value">{{ $requisition->requester?->name }}</dd></div>
                    <div><dt class="detail-label">Created</dt><dd class="detail-value">{{ $requisition->created_at->format('F j, Y') }}</dd></div>
                </dl>
                @if ($requisition->description)
                    <p class="mt-6 whitespace-pre-line text-ink/80">{{ $requisition->description }}</p>
                @endif
            </section>

            <section class="app-card app-card--flush">
                <h3 class="app-card-title px-6 pb-4 pt-6">Items</h3>
                <div class="overflow-x-auto">
                    <table class="data-table">
                        <thead>
                            <tr><th>Item</th><th>Quantity</th><th>Unit price</th><th>Line total</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($requisition->items as $item)
                                <tr>
                                    <td class="min-w-[12rem]">
                                        {{ $item->description }}
                                        @if ($item->specifications)
                                            <span class="block text-ink/60">{{ $item->specifications }}</span>
                                        @endif
                                    </td>
                                    <td class="whitespace-nowrap tabular-nums">{{ rtrim(rtrim((string) $item->quantity, '0'), '.') }} {{ $item->unit }}</td>
                                    <td class="whitespace-nowrap tabular-nums">{{ $item->estimated_unit_price !== null ? $money($item->estimated_unit_price) : 'Not set' }}</td>
                                    <td class="whitespace-nowrap tabular-nums">{{ $item->estimated_unit_price !== null ? $money($item->line_total) : 'Not set' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <p class="border-t border-ink/10 px-6 py-4 text-right font-display text-lg font-bold tabular-nums">Estimated total {{ $money($estimatedTotal) }}</p>
            </section>

            @if (($can['cancel'] && $status !== PrStatus::Draft) || ($can['delete'] && $status === PrStatus::Draft))
                <section class="flex flex-wrap gap-3">
                    @if ($can['cancel'] && $status !== PrStatus::Draft)
                        <form method="POST" action="{{ route('requisitions.cancel', $requisition) }}"
                              onsubmit="return confirm('Cancel this request? This cannot be undone.')">
                            @csrf
                            <button type="submit" class="btn btn-md btn-danger">Cancel request</button>
                        </form>
                    @endif
                    @if ($can['delete'] && $status === PrStatus::Draft)
                        <form method="POST" action="{{ route('requisitions.destroy', $requisition) }}"
                              onsubmit="return confirm('Delete this draft?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-md btn-danger">Delete draft</button>
                        </form>
                    @endif
                </section>
            @endif
        </div>

        <div class="space-y-6">
            <section class="app-card">
                <h3 class="app-card-title">Your procurement officer</h3>
                @if ($requisition->officer)
                    <p class="mt-4 font-semibold">{{ $requisition->officer->name }}</p>
                    <a href="mailto:{{ $requisition->officer->email }}" class="auth-link text-sm">{{ $requisition->officer->email }}</a>
                    @if ($requisition->officer->phone)
                        <p class="mt-1 text-sm text-ink/70">{{ $requisition->officer->phone }}</p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-ink/70">No officer assigned yet. One is assigned after your request is submitted.</p>
                @endif
            </section>

            @if ($requisition->officer && ! $status->isTerminal() && $status !== PrStatus::Draft)
                <section class="app-card">
                    <h3 class="app-card-title">Message your officer</h3>
                    <form method="POST" action="{{ route('requisitions.reply', $requisition) }}" class="mt-4 space-y-3">
                        @csrf
                        <label class="sr-only" for="message">Message</label>
                        <textarea id="message" name="message" rows="3" class="field-input" required></textarea>
                        <x-input-error :messages="$errors->get('message')" />
                        <button type="submit" class="btn btn-md btn-outline-dark">Send note</button>
                    </form>
                </section>
            @endif

            @include('requisitions._activity')
        </div>
    </div>
</x-app-layout>
