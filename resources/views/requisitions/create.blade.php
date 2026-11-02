<x-app-layout>
    <x-slot name="title">New requisition</x-slot>

    @include('requisitions._form', [
        'action' => route('requisitions.store'),
        'method' => 'POST',
    ])
</x-app-layout>
