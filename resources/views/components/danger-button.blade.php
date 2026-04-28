@props(['type' => 'submit'])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'btn-danger']) }}
>
    {{ $slot }}
</button>
