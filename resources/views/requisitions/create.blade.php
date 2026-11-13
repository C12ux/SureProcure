<x-app-layout>
    <x-slot name="section">Requisitions</x-slot>
    <x-slot name="description">Give your procurement officer the details they need to source suppliers.</x-slot>
    <x-slot name="title">New requisition</x-slot>

    @include('requisitions._form', [
        'action' => route('requisitions.store'),
        'method' => 'POST',
    ])
</x-app-layout>
