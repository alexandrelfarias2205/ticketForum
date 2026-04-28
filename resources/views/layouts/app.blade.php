{{--
    Legacy Breeze app layout — delegates to the modern dark x-layouts.app shell.
    Preserves the $header slot used by older Blade pages while applying the
    unified dark glassmorphism design system.
--}}
<x-layouts.app :title="$title ?? 'ticketForum'">
    @isset($header)
        <header class="mb-6">
            <div class="card-compact">
                {{ $header }}
            </div>
        </header>
    @endisset

    {{ $slot }}
</x-layouts.app>
