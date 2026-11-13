@use('App\Enums\OrgRole')
@use('App\Enums\PrStatus')

@php
    $status = $requisition->status;
    $evaluation = $requisition->bidEvaluation;
    $quotationCount = $requisition->quotations->count();
    $owner = $requisition->organization?->users->first(fn ($u) => $u->pivot->org_role === OrgRole::Owner)
        ?? $requisition->organization?->users->first();
    $awarded = $evaluation?->recommendedQuotation;
@endphp

<x-app-layout>
    <x-slot name="section">Assigned requisitions</x-slot>
    <x-slot name="description"><span class="font-mono">{{ $requisition->pr_number }}</span></x-slot>
    <x-slot name="title">{{ $requisition->title }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('officer.requisitions.index') }}" class="btn btn-sm btn-outline-dark">All requests</a>
    </x-slot>

    <div class="flex flex-wrap items-center gap-3">
        <x-status-badge :status="$status" :staff="true" />
    </div>
    <p class="mt-1 text-ink/70">{{ $requisition->organization?->display_name }}</p>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            {{-- ============ Next step ============ --}}
            <section class="app-card">
                <h3 class="app-card-title">Next step</h3>
                <x-input-error :messages="$errors->get('to')" />
                <x-input-error :messages="$errors->get('note')" />

                @if ($status === PrStatus::InReview)
                    <p class="mt-3 text-sm text-ink/70">Check the request with the customer until the details are clear, then start sourcing.</p>

                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5 space-y-3">
                        @csrf
                        <input type="hidden" name="to" value="needs_clarification">
                        <label for="clarify" class="field-label">Something unclear? Ask the customer</label>
                        <textarea id="clarify" name="note" rows="3" class="field-input" placeholder="What do you need to know?" required></textarea>
                        <button type="submit" class="btn btn-md btn-outline-dark">Send question to customer</button>
                    </form>

                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5 border-t border-ink/10 pt-5">
                        @csrf
                        <input type="hidden" name="to" value="sourcing">
                        <button type="submit" class="btn btn-md btn-solid">Details are clear, start sourcing</button>
                    </form>

                @elseif ($status === PrStatus::NeedsClarification)
                    <p class="mt-3 text-sm text-ink/70">Waiting for the customer. When they reply, carry on from where you left off.</p>
                    <div class="mt-5 flex flex-wrap gap-3">
                        <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}">
                            @csrf
                            <input type="hidden" name="to" value="in_review">
                            <button type="submit" class="btn btn-md btn-solid">Resume review</button>
                        </form>
                        @if ($evaluation)
                            <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}">
                                @csrf
                                <input type="hidden" name="to" value="bid_evaluation">
                                <button type="submit" class="btn btn-md btn-outline-dark">Revise the bid evaluation</button>
                            </form>
                        @endif
                    </div>
                    @if ($evaluation?->decision_remarks)
                        <p class="mt-4 text-sm"><span class="font-semibold">Customer note on the evaluation:</span> {{ $evaluation->decision_remarks }}</p>
                    @endif

                @elseif ($status === PrStatus::Sourcing)
                    <p class="mt-3 text-sm text-ink/70">Invite accredited vendors, record the quotations you receive, then move on to evaluating the bids.</p>
                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="to" value="bid_evaluation">
                        @if ($quotationCount === 0)
                            <button type="button" disabled class="btn btn-md btn-solid cursor-not-allowed opacity-50">Move to bid evaluation</button>
                            <p class="mt-2 text-sm text-ink/60">Record at least one quotation first.</p>
                        @else
                            <button type="submit" class="btn btn-md btn-solid">Move to bid evaluation ({{ $quotationCount }} {{ \Illuminate\Support\Str::plural('quotation', $quotationCount) }})</button>
                        @endif
                    </form>

                @elseif ($status === PrStatus::BidEvaluation)
                    <p class="mt-3 text-sm text-ink/70">Score each quotation, choose a recommendation, then send it to the customer for approval.</p>
                    @if ($evaluation?->decision_remarks)
                        <p class="mt-3 text-sm"><span class="font-semibold">Customer note:</span> {{ $evaluation->decision_remarks }}</p>
                    @endif
                    <a href="{{ route('officer.evaluation.edit', $requisition) }}" class="btn btn-md btn-solid mt-5">Open the bid evaluation form</a>

                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5 border-t border-ink/10 pt-5">
                        @csrf
                        <input type="hidden" name="to" value="sourcing">
                        <p class="text-sm text-ink/70">Need another quotation before you decide?</p>
                        <button type="submit" class="btn btn-md btn-outline-dark mt-3">Back to sourcing</button>
                    </form>

                @elseif ($status === PrStatus::AwaitingClientReview)
                    <p class="mt-3 text-sm text-ink/70">The bid evaluation is with the customer for approval. You will see their decision here.</p>

                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5"
                          onsubmit="return confirm('Take this back from the customer? They will not see the evaluation until you send it again.')">
                        @csrf
                        <input type="hidden" name="to" value="bid_evaluation">
                        <button type="submit" class="btn btn-md btn-outline-dark">Take it back to revise</button>
                    </form>

                @elseif ($status === PrStatus::Approved)
                    <p class="mt-3 text-sm text-ink/70">The customer approved the recommendation.</p>
                    @if ($awarded)
                        <p class="mt-3 font-medium">{{ $awarded->vendor?->name }}, {{ $requisition->currency }} {{ number_format((float) $awarded->total_amount, 2) }}</p>
                        <form method="POST" action="{{ route('officer.requisitions.po', $requisition) }}" class="mt-5">
                            @csrf
                            <button type="submit" class="btn btn-md btn-solid">Issue the purchase order</button>
                        </form>
                    @endif

                @elseif ($status === PrStatus::PoIssued)
                    <p class="mt-3 text-sm text-ink/70">The purchase order has been issued. Close the request once the order is complete.</p>
                    <form method="POST" action="{{ route('officer.requisitions.transition', $requisition) }}" class="mt-5">
                        @csrf
                        <input type="hidden" name="to" value="closed">
                        <button type="submit" class="btn btn-md btn-solid">Close request</button>
                    </form>

                @else
                    <p class="mt-3 text-sm text-ink/70">This request is {{ strtolower($status->staffLabel()) }}. There are no further steps.</p>
                @endif
            </section>

            {{-- ============ Sourcing, evaluation, purchase order ============ --}}
            @if ($requisition->invitations->isNotEmpty() || $quotationCount > 0 || $status === PrStatus::Sourcing)
                @include('officer.requisitions._sourcing')
            @endif

            @if ($evaluation && $evaluation->status->value !== 'draft')
                @include('requisitions._evaluation')
            @endif

            @include('requisitions._purchase-order')

            @include('requisitions._summary')
        </div>

        <div class="space-y-6">
            <section class="app-card">
                <h3 class="app-card-title">Customer</h3>
                <p class="mt-4 font-semibold">{{ $requisition->organization?->display_name }}</p>
                @if ($owner)
                    <p class="mt-3 text-sm text-ink/60">Account owner</p>
                    <p class="font-medium">{{ $owner->name }}</p>
                    <a href="mailto:{{ $owner->email }}" class="auth-link text-sm">{{ $owner->email }}</a>
                    @if ($owner->phone)
                        <p class="mt-1 text-sm text-ink/70">{{ $owner->phone }}</p>
                    @endif
                @endif
            </section>

            @unless ($status->isTerminal())
                <section class="app-card">
                    <h3 class="app-card-title">Add a note</h3>
                    <p class="mt-1 text-sm text-ink/70">The customer can see notes in the activity feed.</p>
                    <form method="POST" action="{{ route('officer.requisitions.reply', $requisition) }}" class="mt-4 space-y-3">
                        @csrf
                        <label class="sr-only" for="message">Note</label>
                        <textarea id="message" name="message" rows="3" class="field-input" required></textarea>
                        <x-input-error :messages="$errors->get('message')" />
                        <button type="submit" class="btn btn-md btn-outline-dark">Add note</button>
                    </form>
                </section>
            @endunless

            @include('requisitions._activity', ['staff' => true])
        </div>
    </div>
</x-app-layout>
