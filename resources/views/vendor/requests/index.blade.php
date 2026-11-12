<x-app-layout>
    <x-slot name="title">Requests</x-slot>

    @if ($rows->isEmpty())
        <x-empty-state title="No invitations yet" text="When a customer request matches your categories, your procurement agent invites you to quote and it appears here." />
    @else
        <div class="app-card app-card--flush">
            @include('vendor._requests-table', ['rows' => $rows])
        </div>
        <div class="mt-6">{{ $rows->links() }}</div>
    @endif
</x-app-layout>
