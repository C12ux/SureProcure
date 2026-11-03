<x-app-layout>
    <x-slot name="title">Invitations</x-slot>
    <x-slot name="actions">
        <a href="{{ route('admin.invitations.create') }}" class="btn btn-sm btn-solid inline-flex items-center gap-2">
            <x-icon name="plus" class="h-4 w-4" /> Invite someone
        </a>
    </x-slot>

    {{-- The link is shown once, right after it is created --}}
    @if (session('invite_link'))
        <section class="app-card mb-6 border-jackson/30 bg-jackson/5">
            <h2 class="app-card-title">Send this link to {{ session('invite_for') }}</h2>
            <p class="mt-1 text-sm text-ink/70">
                It works until {{ session('invite_expires') }}. Copy it now: for security it cannot be shown again.
                If you lose it, use "New link".
            </p>
            <x-copy-field class="mt-4" :value="session('invite_link')" />
        </section>
    @endif

    @if ($invitations->isEmpty())
        <x-empty-state title="No invitations yet" text="Vendors and procurement officers join by invitation. Create one and share the link with them.">
            <a href="{{ route('admin.invitations.create') }}" class="btn btn-md btn-solid">Invite someone</a>
        </x-empty-state>
    @else
        <div class="app-card app-card--flush">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Email</th><th>Role</th><th>Status</th><th>Invited</th><th>Actions</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($invitations as $invitation)
                            @php
                                $state = $invitation->status();
                                $tone = ['pending' => 'info', 'accepted' => 'success', 'expired' => 'neutral', 'revoked' => 'neutral'][$state];
                            @endphp
                            <tr>
                                <td class="min-w-[12rem]">
                                    {{ $invitation->email }}
                                    @if ($invitation->company_name)
                                        <span class="block text-ink/60">{{ $invitation->company_name }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">{{ \Illuminate\Support\Str::headline($invitation->role->value) }}</td>
                                <td class="whitespace-nowrap">
                                    <span class="badge badge--{{ $tone }}"><span class="badge-dot"></span>{{ ucfirst($state) }}</span>
                                    @if ($state === 'pending')
                                        <span class="mt-1 block text-xs text-ink/60">until {{ $invitation->expires_at->format('M j') }}</span>
                                    @elseif ($state === 'accepted' && $invitation->accepted_at)
                                        <span class="mt-1 block text-xs text-ink/60">{{ $invitation->accepted_at->format('M j, Y') }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-ink/70">
                                    {{ $invitation->created_at->format('M j, Y') }}
                                    @if ($invitation->inviter)
                                        <span class="block text-xs">by {{ $invitation->inviter->name }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap">
                                    @if ($state === 'pending')
                                        <div class="flex items-center gap-4">
                                            <form method="POST" action="{{ route('admin.invitations.regenerate', $invitation) }}">
                                                @csrf
                                                <button type="submit" class="auth-link text-sm">New link</button>
                                            </form>
                                            <form method="POST" action="{{ route('admin.invitations.revoke', $invitation) }}"
                                                  onsubmit="return confirm('Revoke this invitation? The link will stop working.')">
                                                @csrf
                                                <button type="submit" class="text-sm font-semibold text-danger-deep underline underline-offset-4">Revoke</button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-ink/40">None</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $invitations->links() }}</div>
    @endif
</x-app-layout>
