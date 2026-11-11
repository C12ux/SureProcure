{{-- Vendors invited and quotations received. Needs $requisition, $canSource, $candidates. --}}
@php
    $money = fn ($value) => $requisition->currency.' '.number_format((float) $value, 2);
    $quotedIds = $requisition->quotations->pluck('vendor_id')->all();
    $quotable = $requisition->invitations->filter(fn ($i) => $i->status->value !== 'declined' && ! in_array($i->vendor_id, $quotedIds, true));
    $inviteTone = ['invited' => 'info', 'declined' => 'neutral', 'quoted' => 'success'];
    $inviteText = ['invited' => 'Invited', 'declined' => 'Declined', 'quoted' => 'Quoted'];
    $quoteTone = ['received' => 'neutral', 'shortlisted' => 'info', 'rejected' => 'danger', 'awarded' => 'success'];
    $quoteText = ['received' => 'Received', 'shortlisted' => 'Recommended', 'rejected' => 'Not selected', 'awarded' => 'Awarded'];
@endphp

{{-- Vendors invited --}}
<section class="app-card">
    <h3 class="app-card-title">Vendors invited to quote</h3>

    @if ($requisition->invitations->isEmpty())
        <p class="mt-3 text-sm text-ink/70">No vendors have been invited yet.</p>
    @else
        <ul class="mt-4 divide-y divide-ink/10">
            @foreach ($requisition->invitations as $invitation)
                @php $state = $invitation->status->value; @endphp
                <li class="flex flex-wrap items-center justify-between gap-3 py-3">
                    <p class="font-medium">{{ $invitation->vendor?->name }}</p>
                    <div class="flex items-center gap-4">
                        <span class="badge badge--{{ $inviteTone[$state] }}"><span class="badge-dot"></span>{{ $inviteText[$state] }}</span>
                        @if ($canSource && $state === 'invited')
                            <form method="POST" action="{{ route('officer.requisitions.uninvite', [$requisition, $invitation]) }}" onsubmit="return confirm('Remove this invitation?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-sm font-semibold text-danger-deep underline underline-offset-4">Remove</button>
                            </form>
                        @endif
                    </div>
                </li>
            @endforeach
        </ul>
    @endif

    @if ($canSource)
        <form method="POST" action="{{ route('officer.requisitions.invite', $requisition) }}" class="mt-6 border-t border-ink/10 pt-6">
            @csrf
            <fieldset>
                <legend class="field-label">Invite accredited vendors</legend>
                @if ($candidates->isEmpty())
                    <p class="mt-2 text-sm text-ink/70">There are no more accredited vendors to invite. Vendors appear here once an admin approves their accreditation.</p>
                @else
                    <div class="mt-3 space-y-2">
                        @foreach ($candidates as $vendor)
                            @php $match = $requisition->category_id && $vendor->categories->contains('id', $requisition->category_id); @endphp
                            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-ink/10 p-3 hover:border-ink/30">
                                <input type="checkbox" name="vendor_ids[]" value="{{ $vendor->id }}" class="field-check mt-1">
                                <span class="min-w-0">
                                    <span class="flex flex-wrap items-center gap-2 font-medium">
                                        {{ $vendor->name }}
                                        @if ($match)
                                            <span class="badge badge--success"><span class="badge-dot"></span>Matches this category</span>
                                        @endif
                                    </span>
                                    <span class="block text-sm text-ink/60">{{ $vendor->categories->pluck('name')->join(', ') ?: 'No categories listed' }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                    <x-input-error :messages="$errors->get('vendor_ids')" />
                    <button type="submit" class="btn btn-md btn-solid mt-4">Invite selected vendors</button>
                @endif
            </fieldset>
        </form>
    @endif
</section>

{{-- Quotations --}}
<section class="app-card app-card--flush">
    <h3 class="app-card-title px-6 pb-4 pt-6">Quotations received</h3>

    @if ($requisition->quotations->isEmpty())
        <p class="border-t border-ink/10 px-6 py-6 text-sm text-ink/70">No quotations recorded yet.</p>
    @else
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead><tr><th>Vendor</th><th>Amount</th><th>Delivery</th><th>Valid until</th><th>Status</th><th>File</th><th></th></tr></thead>
                <tbody>
                    @foreach ($requisition->quotations as $quotation)
                        @php $qs = $quotation->status->value; @endphp
                        <tr>
                            <td class="min-w-[10rem] font-medium">
                                {{ $quotation->vendor?->name }}
                                @if ($quotation->payment_terms || $quotation->delivery_terms || $quotation->notes)
                                    <span class="block text-sm font-normal text-ink/60">{{ collect([$quotation->payment_terms, $quotation->delivery_terms, $quotation->notes])->filter()->join(' / ') }}</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap tabular-nums">{{ $money($quotation->total_amount) }}</td>
                            <td class="whitespace-nowrap">{{ $quotation->lead_time_days !== null ? $quotation->lead_time_days.' days' : 'Not given' }}</td>
                            <td class="whitespace-nowrap">{{ $quotation->valid_until?->format('M j, Y') ?? 'Not given' }}</td>
                            <td><span class="badge badge--{{ $quoteTone[$qs] }}"><span class="badge-dot"></span>{{ $quoteText[$qs] }}</span></td>
                            <td class="whitespace-nowrap">
                                @forelse ($quotation->documents as $doc)
                                    <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="auth-link text-sm">View</a>
                                @empty
                                    <span class="text-ink/40">None</span>
                                @endforelse
                            </td>
                            <td class="whitespace-nowrap">
                                @if ($canSource)
                                    <form method="POST" action="{{ route('officer.quotations.destroy', $quotation) }}" onsubmit="return confirm('Remove this quotation?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm font-semibold text-danger-deep underline underline-offset-4">Remove</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if ($canSource && $quotable->isNotEmpty())
        <form method="POST" action="{{ route('officer.quotations.store', $requisition) }}" enctype="multipart/form-data" class="space-y-4 border-t border-ink/10 px-6 py-6">
            @csrf
            <h4 class="font-semibold">Record a quotation</h4>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <x-input-label for="q_vendor" value="Vendor" />
                    <select id="q_vendor" name="vendor_id" class="field-input mt-1.5" required>
                        <option value="">Choose a vendor</option>
                        @foreach ($quotable as $invitation)
                            <option value="{{ $invitation->vendor_id }}" @selected((string) old('vendor_id') === (string) $invitation->vendor_id)>{{ $invitation->vendor?->name }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('vendor_id')" />
                </div>
                <div>
                    <x-input-label for="q_amount" :value="'Total amount ('.$requisition->currency.')'" />
                    <x-text-input id="q_amount" class="mt-1.5" type="number" step="0.01" min="0" name="total_amount" :value="old('total_amount')" required />
                    <x-input-error :messages="$errors->get('total_amount')" />
                </div>
                <div>
                    <x-input-label for="q_lead" value="Delivery time (days)" />
                    <x-text-input id="q_lead" class="mt-1.5" type="number" min="0" name="lead_time_days" :value="old('lead_time_days')" />
                    <x-input-error :messages="$errors->get('lead_time_days')" />
                </div>
                <div>
                    <x-input-label for="q_valid" value="Quotation valid until" />
                    <x-text-input id="q_valid" class="mt-1.5" type="date" name="valid_until" :value="old('valid_until')" />
                    <x-input-error :messages="$errors->get('valid_until')" />
                </div>
                <div>
                    <x-input-label for="q_payment" value="Payment terms" />
                    <x-text-input id="q_payment" class="mt-1.5" type="text" name="payment_terms" :value="old('payment_terms')" placeholder="For example: 30 days after delivery" />
                    <x-input-error :messages="$errors->get('payment_terms')" />
                </div>
                <div>
                    <x-input-label for="q_delivery" value="Delivery terms" />
                    <x-text-input id="q_delivery" class="mt-1.5" type="text" name="delivery_terms" :value="old('delivery_terms')" placeholder="For example: free delivery within Metro Manila" />
                    <x-input-error :messages="$errors->get('delivery_terms')" />
                </div>
            </div>

            <div>
                <x-input-label for="q_notes" value="Notes" />
                <textarea id="q_notes" name="notes" rows="2" class="field-input mt-1.5">{{ old('notes') }}</textarea>
                <x-input-error :messages="$errors->get('notes')" />
            </div>

            <div>
                <x-input-label for="q_file" value="Quotation file (optional)" />
                <input id="q_file" type="file" name="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                       class="mt-1.5 block w-full rounded-xl border border-ink/25 bg-white text-sm file:mr-4 file:cursor-pointer file:border-0 file:bg-mist-200 file:px-4 file:py-3 file:font-semibold file:text-ink">
                <x-input-error :messages="$errors->get('file')" />
            </div>

            <button type="submit" class="btn btn-md btn-solid">Save quotation</button>
        </form>
    @elseif ($canSource)
        <p class="border-t border-ink/10 px-6 py-5 text-sm text-ink/70">Invite vendors above to start recording their quotations.</p>
    @endif
</section>
