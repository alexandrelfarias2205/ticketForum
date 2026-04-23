@props(['title' => 'ticketForum'])
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>{{ $title }} — ticketForum</title>
    @livewireStyles
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 font-sans antialiased">

    {{-- Top bar --}}
    <header class="fixed inset-x-0 top-0 z-20 flex h-16 items-center justify-between bg-white px-6 shadow">
        <a href="{{ route('root.dashboard') }}" class="text-xl font-bold text-indigo-600">ticketForum</a>
        <div class="flex items-center gap-4">
            <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="text-sm text-gray-500 hover:text-red-600 transition-colors">
                    Sair
                </button>
            </form>
        </div>
    </header>

    <div class="flex pt-16 min-h-screen">

        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col w-64 shrink-0 bg-white border-r border-gray-200 fixed inset-y-0 top-16 left-0 z-10 overflow-y-auto">
            <nav class="flex-1 p-4 space-y-1">
                {{-- Grupo 1: Painel --}}
                <a href="{{ route('root.dashboard') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.dashboard'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.dashboard'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                    Painel
                </a>

                <div class="my-2 border-t border-gray-100"></div>

                {{-- Grupo 2: Empresas, Usuários --}}
                <a href="{{ route('root.tenants.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.tenants.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.tenants.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                    </svg>
                    Empresas
                </a>

                <a href="{{ route('root.users.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.users.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.users.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                    Usuários
                </a>

                <div class="my-2 border-t border-gray-100"></div>

                {{-- Grupo 3: Revisões, Votação / Ranking, Entregas --}}
                <a href="{{ route('root.reports.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.reports.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.reports.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 012.012 1.244l.256.512a2.25 2.25 0 002.013 1.244h3.218a2.25 2.25 0 002.013-1.244l.256-.512a2.25 2.25 0 012.013-1.244h3.859m-19.5.338V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18v-4.162c0-.224-.034-.447-.1-.661L19.24 5.338a2.25 2.25 0 00-2.15-1.588H6.911a2.25 2.25 0 00-2.15 1.588L2.1 13.177a2.25 2.25 0 00-.1.661z" />
                    </svg>
                    Revisões
                </a>

                <a href="{{ route('root.voting.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.voting.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.voting.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                    Votação / Ranking
                </a>

                <a href="{{ route('root.delivered.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.delivered.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.delivered.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-.723 3.066 3.745 3.745 0 01-3.066.723 3.745 3.745 0 01-3.068 1.593 3.745 3.745 0 01-3.067-1.593 3.745 3.745 0 01-3.066-.723 3.745 3.745 0 01-.723-3.066A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 01.723-3.066 3.745 3.745 0 013.066-.723A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.066.723 3.745 3.745 0 01.723 3.066A3.745 3.745 0 0121 12z" />
                    </svg>
                    Entregas
                </a>

                <div class="my-2 border-t border-gray-100"></div>

                {{-- Grupo 4: Etiquetas --}}
                <a href="{{ route('root.labels.index') }}"
                   @class([
                       'flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                       'bg-indigo-50 text-indigo-700' => request()->routeIs('root.labels.*'),
                       'text-gray-600 hover:bg-gray-50 hover:text-gray-900' => !request()->routeIs('root.labels.*'),
                   ])>
                    <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    Etiquetas
                </a>
            </nav>
        </aside>

        {{-- Main content --}}
        <main class="flex-1 lg:ml-64 p-6">
            {{ $slot }}
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
                :class="t.type === 'error' ? 'bg-red-600' : 'bg-green-600'"
                class="flex items-center gap-3 rounded-lg px-4 py-3 text-sm text-white shadow-lg min-w-[280px]"
            >
                <span x-text="t.message"></span>
            </div>
        </template>
    </div>

    @livewireScripts
</body>
</html>
