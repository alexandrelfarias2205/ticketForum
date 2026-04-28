<!DOCTYPE html>
<html lang="pt-BR" class="dark">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'ticketForum') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-app min-h-screen font-sans text-slate-100 antialiased">

        <main class="relative isolate flex min-h-screen flex-col items-center justify-center overflow-hidden px-4 py-12">
            {{-- Decorative orbs --}}
            <div class="pointer-events-none absolute -top-32 -left-32 h-96 w-96 rounded-full bg-brand-500/30 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -right-32 h-96 w-96 rounded-full bg-accent-500/30 blur-3xl"></div>

            <header class="relative z-10 mb-12 flex items-center gap-3">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-gradient-brand shadow-glow-brand">
                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75l3 3 7.5-7.5M3 12a9 9 0 1118 0 9 9 0 01-18 0z" />
                    </svg>
                </span>
                <span class="text-2xl font-bold tracking-tight text-white">ticketForum</span>
            </header>

            <div class="relative z-10 max-w-3xl text-center">
                <h1 class="text-balance text-display text-white">
                    Centralize feedback, priorize com votos, entregue mais rápido.
                </h1>
                <p class="mt-6 text-pretty text-base leading-relaxed text-slate-300 sm:text-lg">
                    Plataforma multi-tenant para reportar bugs, sugerir melhorias e priorizar funcionalidades
                    através da votação dos usuários — integrada ao seu Jira ou GitHub.
                </p>

                @if (Route::has('login'))
                    <div class="mt-10 flex flex-wrap items-center justify-center gap-3">
                        @auth
                            <a href="{{ url('/dashboard') }}" class="btn-primary btn-lg">
                                Acessar painel
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="btn-primary btn-lg">
                                Entrar
                            </a>
                            @if (Route::has('register'))
                                <a href="{{ route('register') }}" class="btn-secondary btn-lg">
                                    Criar conta
                                </a>
                            @endif
                        @endauth
                    </div>
                @endif
            </div>

            <footer class="relative z-10 mt-16 text-xs text-slate-500">
                &copy; {{ date('Y') }} ticketForum — Todos os direitos reservados.
            </footer>
        </main>
    </body>
</html>
