@use('App\Enums\AccreditationStatus')

@php
    $o = $vendor->organization;
    $yesNo = fn ($value) => is_null($value) ? 'Not answered' : ($value ? 'Yes' : 'No');
    $docTone = ['pending' => 'neutral', 'verified' => 'success', 'rejected' => 'danger'];
    $docText = ['pending' => 'Awaiting review', 'verified' => 'Verified', 'rejected' => 'Rejected'];
    $size = fn ($bytes) => $bytes >= 1048576 ? number_format($bytes / 1048576, 1).' MB' : number_format(max(1, $bytes) / 1024).' KB';
    $docs = $vendor->documents->sortBy(fn ($d) => $d->type->value);
    $verified = $docs->filter(fn ($d) => $d->status->value === 'verified')->count();
    $underReview = $vendor->accreditation_status === AccreditationStatus::UnderReview;
@endphp

<x-app-layout>
    <x-slot name="section">Vendors</x-slot>
    <x-slot name="description">Review company details, supporting documents, and the accreditation decision.</x-slot>
    <x-slot name="title">{{ $vendor->name }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.vendors.index') }}" class="btn btn-sm btn-outline-dark">All vendors</a>
    </x-slot>

    <div class="flex flex-wrap items-center gap-3">
        <x-vendor-badge :vendor="$vendor" />
        @if ($vendor->submitted_at)
            <span class="text-sm text-ink/70">Submitted {{ $vendor->submitted_at->format('F j, Y') }}@if ($vendor->declared_by), signed by {{ $vendor->declared_by }}@endif</span>
        @else
            <span class="text-sm text-ink/70">Not submitted yet</span>
        @endif
    </div>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            {{-- Company --}}
            <section class="app-card">
                <h2 class="app-card-title">Company</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><dt class="detail-label">Registered name</dt><dd class="detail-value">{{ $o->legal_name }}</dd></div>
                    <div><dt class="detail-label">Trade name</dt><dd class="detail-value">{{ $o->trade_name ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Type</dt><dd class="detail-value">{{ \App\Support\VendorOptions::ORG_TYPES[$o->org_type] ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Year established</dt><dd class="detail-value">{{ $o->year_established ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">TIN</dt><dd class="detail-value">{{ $o->tin ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">VAT registered</dt><dd class="detail-value">{{ $yesNo($vendor->vat_registered) }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Address</dt><dd class="detail-value whitespace-pre-line">{{ $o->business_address ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Website</dt><dd class="detail-value">{{ $o->website ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Bank reference</dt><dd class="detail-value">{{ $vendor->bank_reference ?? 'Not given' }}</dd></div>
                </dl>
            </section>

            <section class="app-card">
                <h2 class="app-card-title">Contact</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><dt class="detail-label">Authorized representative</dt><dd class="detail-value">{{ $vendor->representative_name ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Position</dt><dd class="detail-value">{{ $vendor->representative_title ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Mobile</dt><dd class="detail-value">{{ $vendor->mobile_number ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Landline</dt><dd class="detail-value">{{ $vendor->landline ?? 'Not given' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Email</dt><dd class="detail-value">{{ $o->email }}</dd></div>
                </dl>
            </section>

            <section class="app-card">
                <h2 class="app-card-title">Offering</h2>
                <p class="mt-4 whitespace-pre-line">{{ $vendor->products_services ?? 'Not given' }}</p>
                <p class="mt-5 detail-label">Categories</p>
                <p class="detail-value">{{ $vendor->categories->pluck('name')->join(', ') ?: 'None chosen' }}</p>

                <p class="mt-5 detail-label">Client portfolio</p>
                @forelse ($vendor->clients as $client)
                    <p class="detail-value">{{ $client->client_name }}@if ($client->project_description) <span class="font-normal text-ink/70">, {{ $client->project_description }}</span>@endif</p>
                @empty
                    <p class="detail-value">None listed</p>
                @endforelse
            </section>

            <section class="app-card">
                <h2 class="app-card-title">Capability and compliance</h2>
                <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                    <div><dt class="detail-label">Years in industry</dt><dd class="detail-value">{{ $vendor->years_in_industry ?? 'Not given' }}</dd></div>
                    <div><dt class="detail-label">Employees</dt><dd class="detail-value">{{ $vendor->employees_count ?? 'Not given' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Industries served</dt><dd class="detail-value">{{ collect($vendor->industries_served)->join(', ') ?: 'None chosen' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Capacity</dt><dd class="detail-value whitespace-pre-line">{{ $vendor->service_capacity ?? 'Not given' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Facilities and equipment</dt><dd class="detail-value whitespace-pre-line">{{ $vendor->facilities_equipment ?? 'Not given' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="detail-label">Quality certifications</dt><dd class="detail-value">{{ $vendor->certifications->pluck('name')->join(', ') ?: 'None listed' }}</dd></div>
                    <div class="sm:col-span-2">
                        <dt class="detail-label">Legal disputes or sanctions in the last 5 years</dt>
                        <dd class="detail-value">{{ $yesNo($vendor->has_legal_disputes) }}@if ($vendor->legal_disputes_details) <span class="mt-1 block whitespace-pre-line font-normal">{{ $vendor->legal_disputes_details }}</span>@endif</dd>
                    </div>
                    <div class="sm:col-span-2">
                        <dt class="detail-label">Complies with HSE standards</dt>
                        <dd class="detail-value">{{ $yesNo($vendor->complies_hse) }}@if ($vendor->hse_details) <span class="mt-1 block whitespace-pre-line font-normal">{{ $vendor->hse_details }}</span>@endif</dd>
                    </div>
                </dl>
            </section>

            {{-- Documents --}}
            <section class="app-card app-card--flush">
                <div class="px-6 pb-4 pt-6">
                    <h2 class="app-card-title">Documents</h2>
                    <p class="mt-1 text-sm text-ink/70">{{ $verified }} of {{ $docs->count() }} verified. Files open in a new tab through a link that expires in 5 minutes.</p>
                </div>
                @if ($docs->isEmpty())
                    <p class="border-t border-ink/10 px-6 py-6 text-sm text-ink/70">Nothing uploaded yet.</p>
                @else
                    <div class="overflow-x-auto">
                        <table class="data-table">
                            <thead><tr><th>Document</th><th>File</th><th>Status</th><th>Review</th></tr></thead>
                            <tbody>
                                @foreach ($docs as $doc)
                                    @php $state = $doc->status->value; @endphp
                                    <tr>
                                        <td class="min-w-[10rem] font-medium">{{ $doc->type->label() }}</td>
                                        <td class="min-w-[10rem]">
                                            <a href="{{ route('documents.show', $doc) }}" target="_blank" rel="noopener" class="auth-link break-all">{{ $doc->original_name }}</a>
                                            <span class="block text-xs text-ink/60">{{ $size($doc->size_bytes ?? 0) }}, {{ $doc->created_at->format('M j, Y') }}</span>
                                        </td>
                                        <td><span class="badge badge--{{ $docTone[$state] }}"><span class="badge-dot"></span>{{ $docText[$state] }}</span></td>
                                        <td class="whitespace-nowrap">
                                            <div class="flex items-center gap-4">
                                                @if ($state !== 'verified')
                                                    <form method="POST" action="{{ route('admin.documents.review', $doc) }}">
                                                        @csrf
                                                        <input type="hidden" name="action" value="verify">
                                                        <button type="submit" class="auth-link text-sm">Verify</button>
                                                    </form>
                                                @endif
                                                @if ($state !== 'rejected')
                                                    <form method="POST" action="{{ route('admin.documents.review', $doc) }}">
                                                        @csrf
                                                        <input type="hidden" name="action" value="reject">
                                                        <button type="submit" class="text-sm font-semibold text-danger-deep underline underline-offset-4">Reject</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        {{-- Decision --}}
        <div class="space-y-6">
            <section class="app-card">
                <h2 class="app-card-title">Decision</h2>

                @if ($underReview)
                    <form method="POST" action="{{ route('admin.vendors.decide', $vendor) }}" class="mt-5 space-y-4"
                          x-data="{ action: '{{ old('action', '') }}' }">
                        @csrf

                        <fieldset>
                            <legend class="sr-only">Choose a decision</legend>
                            <div class="space-y-2">
                                <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="approve" x-model="action" class="field-check rounded-full" required> Approve and accredit</label>
                                <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="changes" x-model="action" class="field-check rounded-full"> Send back for changes</label>
                                <label class="flex items-center gap-3 text-sm"><input type="radio" name="action" value="reject" x-model="action" class="field-check rounded-full"> Reject</label>
                            </div>
                            <x-input-error :messages="$errors->get('action')" />
                        </fieldset>

                        <div x-show="action === 'approve'" x-cloak>
                            <x-input-label for="expires_on" value="Accredited until" />
                            <x-text-input id="expires_on" class="mt-1.5" name="expires_on" type="date" :value="old('expires_on', now()->addYear()->toDateString())" />
                            <p class="field-hint">Defaults to 12 months, matching the annual subscription.</p>
                            <x-input-error :messages="$errors->get('expires_on')" />
                        </div>

                        <div x-show="action !== ''" x-cloak>
                            <x-input-label for="note" value="Note" />
                            <textarea id="note" name="note" rows="4" class="field-input mt-1.5">{{ old('note') }}</textarea>
                            <p class="field-hint" x-show="action === 'changes' || action === 'reject'">The vendor sees this note. Be specific.</p>
                            <x-input-error :messages="$errors->get('note')" />
                        </div>

                        <button type="submit" class="btn btn-md btn-solid" x-show="action !== ''" x-cloak>Confirm decision</button>
                    </form>

                    @if ($verified < $docs->count())
                        <p class="mt-4 text-sm text-ink/70">{{ $docs->count() - $verified }} document(s) are not verified yet. You can still decide.</p>
                    @endif
                @else
                    <p class="mt-3 text-sm text-ink/70">This application is not waiting for a decision.</p>
                    @if ($vendor->reviewer)
                        <p class="mt-3 text-sm">Last reviewed by {{ $vendor->reviewer->name }}@if ($vendor->reviewed_at), {{ $vendor->reviewed_at->format('M j, Y') }}@endif.</p>
                    @endif
                    @if ($vendor->rejection_reason)
                        <p class="mt-2 text-sm"><span class="font-semibold">Rejection reason:</span> {{ $vendor->rejection_reason }}</p>
                    @endif
                    @if ($vendor->remarks)
                        <p class="mt-2 text-sm"><span class="font-semibold">Note:</span> {{ $vendor->remarks }}</p>
                    @endif
                @endif
            </section>

            <section class="app-card">
                <h2 class="app-card-title">Completeness</h2>
                <p class="mt-2 text-sm font-semibold">{{ $accreditation->percent() }}% of the form is filled in</p>
                <div class="bar-track" role="progressbar" aria-valuenow="{{ $accreditation->percent() }}" aria-valuemin="0" aria-valuemax="100">
                    <div class="bar-fill bar-fill--best" style="width: {{ $accreditation->percent() }}%; --d: 100ms;"></div>
                </div>
                @foreach ($accreditation->sections() as $section)
                    @if (! $section['done'])
                        <p class="mt-4 text-sm"><span class="font-semibold">{{ $section['label'] }}, missing:</span> {{ implode(', ', $section['missing']) }}</p>
                    @endif
                @endforeach
            </section>
        </div>
    </div>
</x-app-layout>
