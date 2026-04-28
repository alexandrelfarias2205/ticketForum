<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Esqueci minha senha</h2>
        <p class="mt-2 text-sm text-slate-400">
            Sem problemas. Informe o seu e-mail e enviaremos um link para você criar uma nova senha.
        </p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email"
                          :value="old('email')" :error="$errors->has('email')"
                          required autofocus
                          placeholder="seu@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <x-primary-button class="w-full">
            Enviar link de redefinição
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        Lembrou a senha?
        <a href="{{ route('login') }}"
           class="font-semibold text-brand-300 transition hover:text-brand-200 focus-ring rounded">
            Voltar ao login
        </a>
    </p>
</x-guest-layout>
