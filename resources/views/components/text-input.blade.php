@props(['disabled' => false, 'error' => false])

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'input-dark' . ($error ? ' input-dark-error' : '')
    ]) }}
>
