@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'mb-4 flex items-center gap-2 rounded-lg border border-success-400/30 bg-success-500/10 px-3 py-2 text-sm font-medium text-success-300']) }}
         role="status"
    >
        <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>{{ $status }}</span>
    </div>
@endif
