<x-app-layout>
    <x-slot name="title">Edit {{ $requisition->pr_number }}</x-slot>

    @include('requisitions._form', [
        'action' => route('requisitions.update', $requisition),
        'method' => 'PUT',
    ])
</x-app-layout>
