@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'flex items-center gap-3 rounded-lg bg-gradient-brand-soft px-4 py-2 text-base font-medium text-white ring-1 ring-inset ring-brand-400/30'
    : 'flex items-center gap-3 rounded-lg px-4 py-2 text-base font-medium text-slate-300 transition hover:bg-white/5 hover:text-white';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
