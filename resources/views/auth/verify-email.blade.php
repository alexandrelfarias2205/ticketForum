<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Verifique seu e-mail</h2>
        <p class="mt-2 text-sm text-slate-400">
            Obrigado por se cadastrar! Antes de começar, confirme seu e-mail clicando no link que enviamos.
            Se não recebeu, podemos enviar outro.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 flex items-start gap-2 rounded-lg border border-success-400/30 bg-success-500/10 px-3 py-2 text-sm text-success-300" role="status">
            <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <span>Um novo link de verificação foi enviado para o e-mail informado durante o cadastro.</span>
        </div>
    @endif

    <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                Reenviar e-mail de verificação
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                    class="text-sm font-medium text-slate-400 transition hover:text-slate-200 focus-ring rounded">
                Sair
            </button>
        </form>
    </div>
</x-guest-layout>
