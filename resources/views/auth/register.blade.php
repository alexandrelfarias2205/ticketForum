<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Criar conta</h2>
        <p class="mt-1 text-sm text-slate-400">Comece a usar o ticketForum em poucos segundos.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="name" value="Nome completo" />
            <x-text-input id="name" class="mt-1.5" type="text" name="name"
                          :value="old('name')" :error="$errors->has('name')"
                          required autofocus autocomplete="name"
                          placeholder="Como prefere ser chamado" />
            <x-input-error :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email"
                          :value="old('email')" :error="$errors->has('email')"
                          required autocomplete="username"
                          placeholder="seu@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password"
                          :error="$errors->has('password')"
                          required autocomplete="new-password"
                          placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirme a senha" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation"
                          :error="$errors->has('password_confirmation')"
                          required autocomplete="new-password"
                          placeholder="Repita a senha" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">
            Criar conta
        </x-primary-button>
    </form>

    <p class="mt-6 text-center text-sm text-slate-400">
        Já tem uma conta?
        <a href="{{ route('login') }}"
           class="font-semibold text-brand-300 transition hover:text-brand-200 focus-ring rounded">
            Entrar
        </a>
    </p>
</x-guest-layout>
