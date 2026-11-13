<x-app-layout>
    <x-slot name="section">Requisitions</x-slot>
    <x-slot name="description">Update the request details and line items.</x-slot>
    <x-slot name="title">Edit {{ $requisition->pr_number }}</x-slot>

    @include('requisitions._form', [
        'action' => route('requisitions.update', $requisition),
        'method' => 'PUT',
    ])
</x-app-layout>
