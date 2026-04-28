<!DOCTYPE html>
<html lang="pt-BR" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ticketForum') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-app min-h-screen font-sans text-slate-100 antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            {{-- Hero (desktop only) --}}
            <aside class="relative hidden overflow-hidden lg:flex lg:flex-col lg:justify-between lg:p-12 xl:p-16">
                {{-- Decorative gradient orbs --}}
                <div class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-accent-500/30 blur-3xl"></div>
                <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_center,rgba(99,102,241,0.05),transparent_60%)]"></div>

                <div class="relative z-10 flex items-center gap-3">
                    <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-brand shadow-glow-brand">
                        <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3 7.5-7.5M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                        </svg>
                    </span>
                    <span class="text-xl font-bold tracking-tight text-white">ticketForum</span>
                </div>

                <div class="relative z-10 max-w-lg">
                    <h1 class="text-balance text-display text-white">
                        Centralize feedback, priorize com votos, entregue mais rápido.
                    </h1>
                    <p class="mt-6 text-base leading-relaxed text-slate-300">
                        Plataforma multi-tenant para reportar bugs, sugerir melhorias e priorizar funcionalidades através
                        de votação dos usuários — integrada ao seu Jira ou GitHub.
                    </p>

                    <ul class="mt-8 space-y-3">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-success-500/20 text-success-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="text-sm text-slate-300">Bugs e melhorias em um único lugar</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-success-500/20 text-success-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="text-sm text-slate-300">Votação e ranking transparentes</span>
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 inline-flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-success-500/20 text-success-400">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                            </span>
                            <span class="text-sm text-slate-300">Sincronização automática com Jira e GitHub</span>
                        </li>
                    </ul>
                </div>

                <p class="relative z-10 text-xs text-slate-500">
                    &copy; {{ date('Y') }} ticketForum — Todos os direitos reservados.
                </p>
            </aside>

            {{-- Form panel --}}
            <main class="flex flex-col justify-center px-4 py-10 sm:px-8 lg:px-12 xl:px-16">
                <div class="mx-auto w-full max-w-md">
                    {{-- Mobile logo --}}
                    <div class="mb-8 flex items-center gap-3 lg:hidden">
                        <span class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-brand shadow-glow-brand">
                            <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3 7.5-7.5M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                            </svg>
                        </span>
                        <span class="text-xl font-bold tracking-tight text-white">ticketForum</span>
                    </div>

                    <div class="card-compact sm:card animate-slide-up">
                        {{ $slot }}
                    </div>
                </div>
            </main>
        </div>
    </body>
</html>
