@props(['title' => 'ticketForum'])
<!DOCTYPE html>
<html lang="pt-BR" class="dark">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>{{ $title }} — ticketForum</title>

    {{-- Inter via Bunny Fonts --}}
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
    @endphp

    {{-- Topbar --}}
    <header class="fixed inset-x-0 top-0 z-30 flex h-16 items-center justify-between border-b border-white/10 bg-surface-900/70 px-4 backdrop-blur-xl sm:px-6">
        <div class="flex items-center gap-3">
            {{-- Mobile hamburger --}}
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

            <a href="{{ route('app.dashboard') }}" class="flex items-center gap-2">
                <span class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-brand shadow-glow-brand">
                    <svg class="h-4 w-4 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3 7.5-7.5M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                </span>
                <span class="text-base font-bold tracking-tight text-white">ticketForum</span>
            </a>
        </div>

        <div class="flex items-center gap-3">
            <div class="hidden text-right sm:block">
                <p class="text-sm font-semibold text-white leading-tight">{{ $user->name }}</p>
                <p class="text-xs text-slate-400 leading-tight">{{ $user->role->label() ?? '' }}</p>
            </div>

            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="avatar-initials focus-ring transition hover:opacity-90" aria-label="Menu do usuário">
                        {{ $initials ?: 'U' }}
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
        {{-- Sidebar (desktop fixed, mobile drawer) --}}
        <aside
            class="fixed inset-y-0 top-16 left-0 z-20 w-64 shrink-0 transform border-r border-white/10 bg-surface-900/80 backdrop-blur-xl transition-transform duration-200 ease-out lg:translate-x-0"
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
        >
            <nav class="flex h-full flex-col p-3">
                <div class="flex-1 space-y-1">
                    <a href="{{ route('app.dashboard') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.dashboard') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                        Painel
                    </a>

                    @if(auth()->user()->role->value === 'tenant_admin')
                        <a href="{{ route('app.products.index') }}"
                           @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.products.*') ])>
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" /></svg>
                            Produtos
                        </a>
                    @endif

                    <a href="{{ route('app.reports.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.reports.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        Meus tickets
                    </a>

                    <a href="{{ route('app.voting.index') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.voting.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" />
                        </svg>
                        Em votação
                    </a>

                    @if($user->isTenantAdmin())
                        <div class="my-3 divider-dark"></div>
                        <p class="px-3 pb-1 text-[0.65rem] font-semibold uppercase tracking-wider text-slate-500">Administração</p>
                        <a href="{{ route('app.admin.users.index') }}"
                           @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.admin.users.*') ])>
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            Usuários
                        </a>
                    @endif
                </div>

                @if(auth()->user()->role->value === 'tenant_admin')
                    <a href="{{ route('app.settings.integrations') }}"
                       @class([ 'nav-link', 'nav-link-active' => request()->routeIs('app.settings.*') ])>
                        <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                        Integrações
                    </a>
                @endif

                <a href="{{ route('profile.edit') }}"
                   @class([ 'nav-link mt-2', 'nav-link-active' => request()->routeIs('profile.*') ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" /></svg>
                    Perfil
                </a>
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
