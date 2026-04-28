@props(['title' => 'ticketForum'])
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — ticketForum</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-app min-h-screen font-sans text-slate-100 antialiased"
      x-data="{ sidebarOpen: false }">

    @php
        $user = auth()->user();
        $initials = collect(explode(' ', trim($user->name)))
            ->filter()
            ->take(2)
            ->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))
            ->implode('');

        // Pending review count badge
        $pendingReviewCount = 0;
        try {
            $pendingReviewCount = \App\Models\Report::query()
                ->withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                ->where('status', \App\Enums\ReportStatus::PendingReview->value)
                ->count();
        } catch (\Throwable) {
            // Don't break the layout if model resolution fails
        }
    @endphp

    {{-- Topbar --}}
    <header class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-white/10 bg-surface-900/70 px-4 backdrop-blur-xl sm:px-6">
        <div class="flex items-center gap-3">
            <button
                type="button"
                @click="sidebarOpen = ! sidebarOpen"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg text-slate-300 transition hover:bg-white/5 hover:text-white lg:hidden focus-ring"
                aria-label="Abrir menu"
            >
                <svg x-show="!sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                </svg>
                <svg x-show="sidebarOpen" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="display:none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <a href="{{ route('root.dashboard') }}" class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-brand shadow-glow-brand">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3 7.5-7.5M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                </span>
                <span class="text-base font-bold tracking-tight text-white">
                    ticketForum
                    <span class="ml-1 rounded bg-accent-500/20 px-1.5 py-0.5 align-middle text-[0.6rem] font-semibold uppercase text-accent-300">root</span>
                </span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-white leading-tight">{{ $user->name }}</p>
                <p class="text-xs text-slate-400 leading-tight">Plataforma</p>
            </div>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="avatar-initials focus-ring transition hover:opacity-90" aria-label="Menu do usuário">
                        {{ $initials ?: 'R' }}
                    </button>
                </x-slot>
                <x-slot name="content">
                    <a href="{{ route('profile.edit') }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-slate-200 hover:bg-white/5 hover:text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                        Perfil
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="flex w-full items-center gap-2 px-4 py-2 text-left text-sm text-slate-200 hover:bg-danger-500/10 hover:text-danger-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" /></svg>
                            Sair
                        </button>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </header>

    <div class="flex pt-16 min-h-screen">
        {{-- Sidebar --}}
        <aside
            class="fixed inset-y-0 top-16 left-0 z-20 w-64 shrink-0 transform border-r border-white/10 bg-surface-900/80 backdrop-blur-xl transition-transform duration-200 ease-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <nav class="flex h-full flex-col overflow-y-auto p-3">
                <div class="flex-1 space-y-1">
                    <a href="{{ route('root.dashboard') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.dashboard') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                        Painel
                    </a>

                    <p class="px-3 pb-1 pt-3 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Cadastros</p>

                    <a href="{{ route('root.tenants.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.tenants.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" /></svg>
                        Empresas
                    </a>

                    <a href="{{ route('root.users.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.users.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" /></svg>
                        Usuários
                    </a>

                    <p class="px-3 pb-1 pt-3 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Operação</p>

                    <a href="{{ route('root.reports.index') }}"
                       @class([ 'nav-link justify-between', 'nav-link-active' => request()->routeIs('root.reports.*') ])>
                        <span class="flex items-center gap-3">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.1 13.177a2.25 2.25 0 00-.1.661z" /></svg>
                            Fila de revisão
                        </span>
                        @if ($pendingReviewCount > 0)
                            <span class="badge badge-warning">{{ $pendingReviewCount }}</span>
                        @endif
                    </a>

                    <a href="{{ route('root.voting.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.voting.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" /></svg>
                        Ranking de votos
                    </a>

                    <a href="{{ route('root.delivered.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.delivered.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-.723 3.066 3.745 3.745 0 01-3.066.723 3.745 3.745 0 01-3.068 1.593 3.745 3.745 0 01-3.067-1.593 3.745 3.745 0 01-3.066-.723 3.745 3.745 0 01-.723-3.066A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 01.723-3.066 3.745 3.745 0 013.066-.723A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.066.723 3.745 3.745 0 01.723 3.066A3.745 3.745 0 0121 12z" /></svg>
                        Entregas
                    </a>

                    <p class="px-3 pb-1 pt-3 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Configurações</p>

                    <a href="{{ route('root.labels.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.labels.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                        Etiquetas
                    </a>

                    <a href="{{ route('root.agent.dashboard') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('root.agent.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.847.814a4.5 4.5 0 00-3.09 3.09zM18 18.75l-.5-1.75-1.75-.5 1.75-.5.5-1.75.5 1.75 1.75.5-1.75.5-.5 1.75z" /></svg>
                        Agente
                    </a>
                </div>
            </nav>
        </aside>

        {{-- Mobile overlay --}}
        <div
            x-show="sidebarOpen"
            x-transition.opacity
            @click="sidebarOpen = false"
            class="fixed inset-0 top-16 z-10 bg-black/60 backdrop-blur-sm lg:hidden"
            style="display:none"
        ></div>

        {{-- Main --}}
        <main class="flex-1 lg:ml-64">
            <div class="p-4 sm:p-6 lg:p-8">
                {{ $slot }}
            </div>
        </main>
    </div>

    {{-- Toast container --}}
    <div
        x-data="toast"
        x-on:notify.window="show($event.detail)"
        class="fixed bottom-4 right-4 z-50 flex flex-col gap-2"
    >
        <template x-for="(t, i) in toasts" :key="i">
            <div
                x-show="t.visible"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-2"
                x-transition:enter-end="opacity-100 translate-y-0"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                :class="t.type === 'error'
                    ? 'border-danger-400/40 bg-danger-500/15 text-danger-100'
                    : 'border-success-400/40 bg-success-500/15 text-success-100'"
                class="flex min-w-[280px] items-center gap-3 rounded-xl border px-4 py-3 text-sm shadow-glass backdrop-blur-xl"
            >
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
