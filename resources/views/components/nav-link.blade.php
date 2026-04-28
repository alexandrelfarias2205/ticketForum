@props(['active' => false])

@php
$classes = ($active ?? false)
    ? 'inline-flex items-center gap-2 border-b-2 border-brand-400 px-1 pt-1 text-sm font-medium text-white transition'
    : 'inline-flex items-center gap-2 border-b-2 border-transparent px-1 pt-1 text-sm font-medium text-slate-400 transition hover:border-white/20 hover:text-slate-100';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
