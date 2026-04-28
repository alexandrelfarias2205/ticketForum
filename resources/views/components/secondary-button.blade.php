@props(['type' => 'button'])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'btn-secondary']) }}
>
    {{ $slot }}
</button>
