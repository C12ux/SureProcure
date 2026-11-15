<x-app-layout>
    <x-slot name="section">Vendor inquiries</x-slot>
    <x-slot name="title">Vendor inquiries</x-slot>
    <x-slot name="description">Review prospective suppliers and prepare an invitation if you want to proceed.</x-slot>

    @forelse ($inquiries as $inquiry)
        <article class="app-card mb-4">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0 break-words">
                    <h2 class="app-card-title">{{ $inquiry->company_name }}</h2>
                    <p class="mt-1 text-sm text-ink/70">{{ $inquiry->contact_name }} &middot; {{ $inquiry->email }}</p>
                    <p class="mt-1 text-sm text-ink/70">{{ $inquiry->created_at->format('M j, Y') }}</p>
                </div>
                <a href="{{ route('admin.vendor-inquiries.invite', $inquiry) }}" class="btn btn-sm btn-outline-dark">Prepare invitation</a>
            </div>
            <p class="mt-4 whitespace-pre-wrap break-words">{{ $inquiry->message }}</p>
        </article>
    @empty
        <x-empty-state title="No vendor inquiries yet" text="New inquiries from the landing page will appear here." />
    @endforelse

    {{ $inquiries->links() }}
</x-app-layout>
