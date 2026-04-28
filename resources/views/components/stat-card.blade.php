@props([
    'label' => '',
    'value' => '—',
    'icon' => null,
    'tone' => 'brand',          // brand|accent|success|warning|danger|info|neutral
    'delta' => null,            // signed integer
    'deltaLabel' => null,       // "vs semana anterior" etc.
    'href' => null,
    'hint' => null,
])

@php
    $iconRing = match($tone) {
        'success' => 'bg-success-500/15 text-success-400 ring-success-400/30',
        'warning' => 'bg-warning-500/15 text-warning-400 ring-warning-400/30',
        'danger'  => 'bg-danger-500/15 text-danger-400 ring-danger-400/30',
        'info'    => 'bg-info-500/15 text-info-400 ring-info-400/30',
        'accent'  => 'bg-accent-500/15 text-accent-300 ring-accent-400/30',
        'neutral' => 'bg-slate-500/15 text-slate-300 ring-slate-400/30',
        default   => 'bg-brand-500/15 text-brand-300 ring-brand-400/30',
    };

    $deltaTone = null;
    if ($delta !== null) {
        if ($delta > 0)      { $deltaTone = ['symbol' => '▲', 'class' => 'text-success-400']; }
        elseif ($delta < 0)  { $deltaTone = ['symbol' => '▼', 'class' => 'text-danger-400']; }
        else                 { $deltaTone = ['symbol' => '■', 'class' => 'text-slate-500']; }
    }

    $tag = $href ? 'a' : 'div';
    $hoverClass = $href ? ' card-hover cursor-pointer' : '';
@endphp

<{{ $tag }}
    @if($href) href="{{ $href }}" @endif
    {{ $attributes->merge(['class' => 'card relative flex flex-col gap-3' . $hoverClass]) }}
>
    <div class="flex items-start justify-between gap-3">
        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">{{ $label }}</p>
        @if(isset($icon) && $icon->isNotEmpty())
            <span class="inline-flex h-9 w-9 shrink-0 items-center justify-center rounded-lg ring-1 ring-inset {{ $iconRing }}">
                {{ $icon }}
            </span>
        @endif
    </div>

    <p class="text-3xl font-bold text-white">{{ $value }}</p>

    @if($delta !== null)
        <p class="flex items-center gap-1.5 text-xs {{ $deltaTone['class'] }}">
            <span aria-hidden="true">{{ $deltaTone['symbol'] }}</span>
            <span>{{ abs($delta) }}{{ $deltaLabel ? ' ' . $deltaLabel : '' }}</span>
        </p>
    @elseif($hint)
        <p class="text-xs text-slate-500">{{ $hint }}</p>
    @endif
</{{ $tag }}>
