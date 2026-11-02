@props(['type' => 'success'])

<div {{ $attributes->merge(['class' => 'alert alert--'.$type, 'role' => 'status']) }}>{{ $slot }}</div>
