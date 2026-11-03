@use('App\Enums\OrgRole')

<x-app-layout>
    <x-slot name="title">Customers</x-slot>

    @if ($customers->isEmpty())
        <x-empty-state title="No customers yet" text="Companies appear here when they register on the site." />
    @else
        <div class="app-card app-card--flush">
            <div class="overflow-x-auto">
                <table class="data-table">
                    <thead>
                        <tr><th>Company</th><th>Account owner</th><th>People</th><th>Requests</th><th>Subscription</th><th>Joined</th></tr>
                    </thead>
                    <tbody>
                        @foreach ($customers as $customer)
                            @php
                                $owner = $customer->users->first(fn ($u) => $u->pivot->org_role === OrgRole::Owner) ?? $customer->users->first();
                            @endphp
                            <tr>
                                <td class="min-w-[10rem] font-semibold">{{ $customer->display_name }}</td>
                                <td class="min-w-[12rem]">
                                    @if ($owner)
                                        {{ $owner->name }}
                                        <span class="block text-ink/60">{{ $owner->email }}</span>
                                    @else
                                        <span class="text-ink/60">None</span>
                                    @endif
                                </td>
                                <td class="tabular-nums">{{ $customer->users_count }}</td>
                                <td class="tabular-nums">{{ $customer->submitted_count }}</td>
                                <td>
                                    @if ($customer->subscribed)
                                        <span class="badge badge--success"><span class="badge-dot"></span>Active</span>
                                    @else
                                        <span class="badge badge--neutral"><span class="badge-dot"></span>None</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-ink/70">{{ $customer->created_at->format('M j, Y') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="mt-6">{{ $customers->links() }}</div>
    @endif
</x-app-layout>
