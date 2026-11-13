<x-app-layout>
    <x-slot name="section">Invited requests</x-slot>
    <x-slot name="description">View requests your procurement agent invited you to quote on.</x-slot>
    <x-slot name="title">Invited requests</x-slot>

    @if ($rows->isEmpty())
        <x-empty-state title="No invitations yet" text="When a customer request matches your categories, your procurement agent invites you to quote and it appears here." />
    @else
        <div class="app-card app-card--flush">
            @include('vendor._requests-table', ['rows' => $rows])
        </div>
        <div class="mt-6">{{ $rows->links() }}</div>
    @endif
</x-app-layout>
