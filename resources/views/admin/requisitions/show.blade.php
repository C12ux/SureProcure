@use('App\Enums\OrgRole')

@php
    $owner = $requisition->organization?->users->first(fn ($u) => $u->pivot->org_role === OrgRole::Owner)
        ?? $requisition->organization?->users->first();
@endphp

<x-app-layout>
    <x-slot name="section">Requisitions</x-slot>
    <x-slot name="description"><span class="font-mono">{{ $requisition->pr_number }}</span></x-slot>
    <x-slot name="title">{{ $requisition->title }}</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.requisitions.index') }}" class="btn btn-sm btn-outline-dark">All requests</a>
    </x-slot>

    <div class="flex flex-wrap items-center gap-3">
        <x-status-badge :status="$requisition->status" :staff="true" />
    </div>
    <p class="mt-1 text-ink/70">{{ $requisition->organization?->display_name }}</p>

    <div class="mt-6 grid gap-6 xl:grid-cols-3">

        <div class="space-y-6 xl:col-span-2">

            {{-- Manage --}}
            <section class="app-card">
                <h3 class="app-card-title">Manage this request</h3>

                <form method="POST" action="{{ route('admin.requisitions.assign', $requisition) }}" class="mt-5">
                    @csrf
                    <label for="officer_id" class="field-label">Procurement officer</label>
                    @if ($officers->isEmpty())
                        <p class="mt-1.5 text-sm text-ink/70">
                            There are no procurement officers yet.
                            <a href="{{ route('admin.invitations.create') }}" class="auth-link">Invite one</a>
                        </p>
                    @else
                        <div class="mt-1.5 flex flex-col gap-2 sm:flex-row">
                            <select id="officer_id" name="officer_id" class="field-input" required>
                                <option value="" @selected(! $requisition->assigned_officer_id)>Choose an officer</option>
                                @foreach ($officers as $officer)
                                    <option value="{{ $officer->id }}" @selected((int) $requisition->assigned_officer_id === $officer->id)>{{ $officer->name }} ({{ $officer->email }})</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-md btn-solid shrink-0">{{ $requisition->assigned_officer_id ? 'Reassign' : 'Assign' }}</button>
                        </div>
                        <x-input-error :messages="$errors->get('officer_id')" />
                    @endif
                </form>

                <hr class="my-6 border-ink/10">

                @if ($transitions->isEmpty())
                    <p class="text-sm text-ink/70">This request is {{ strtolower($requisition->status->staffLabel()) }}. There are no further steps.</p>
                @else
                    <form method="POST" action="{{ route('admin.requisitions.transition', $requisition) }}" class="space-y-4" x-data="{ to: '{{ old('to') }}' }">
                        @csrf
                        <div>
                            <label for="to" class="field-label">Move to</label>
                            <select id="to" name="to" class="field-input mt-1.5" x-model="to" required>
                                <option value="">Choose the next step</option>
                                @foreach ($transitions as $next)
                                    <option value="{{ $next->value }}">{{ $next->staffLabel() }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('to')" />
                        </div>
                        <div>
                            <label for="note" class="field-label">Note</label>
                            <textarea id="note" name="note" rows="3" class="field-input mt-1.5" placeholder="Shown in the activity feed">{{ old('note') }}</textarea>
                            <p class="field-hint" x-show="['needs_clarification', 'rejected'].includes(to)" x-cloak>The customer sees this note, so say what you need or why.</p>
                            <x-input-error :messages="$errors->get('note')" />
                        </div>
                        <button type="submit" class="btn btn-md btn-solid">Update status</button>
                    </form>
                @endif
            </section>

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

            <section class="app-card">
                <h3 class="app-card-title">Assigned officer</h3>
                @if ($requisition->officer)
                    <p class="mt-4 font-semibold">{{ $requisition->officer->name }}</p>
                    <a href="mailto:{{ $requisition->officer->email }}" class="auth-link text-sm">{{ $requisition->officer->email }}</a>
                @else
                    <p class="mt-3 text-sm text-ink/70">Nobody yet.</p>
                @endif
            </section>

            @include('requisitions._activity', ['staff' => true])
        </div>
    </div>
</x-app-layout>
