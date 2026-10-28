<button {{ $attributes->merge(['type' => 'submit', 'class' => 'btn btn-lg btn-solid']) }}>
    {{ $slot }}
</button>
