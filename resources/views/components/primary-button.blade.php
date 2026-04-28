@props(['type' => 'submit', 'loading' => false])

<button
    type="{{ $type }}"
    {{ $attributes->merge(['class' => 'btn-primary']) }}
>
    @if ($loading)
        <svg class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true">
            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
        </svg>
    @endif
    {{ $slot }}
</button>
