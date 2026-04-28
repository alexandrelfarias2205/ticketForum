<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Bem-vindo de volta</h2>
        <p class="mt-1 text-sm text-slate-400">Entre na sua conta para continuar.</p>
    </div>

    <x-auth-session-status :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        {{-- Email --}}
        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email"
                          class="mt-1.5"
                          type="email"
                          name="email"
                          :value="old('email')"
                          :error="$errors->has('email')"
                          required autofocus autocomplete="username"
                          placeholder="seu@email.com" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        {{-- Senha --}}
        <div>
            <div class="flex items-center justify-between">
                <x-input-label for="password" value="Senha" />
                @if (Route::has('password.request'))
                    <a class="text-xs font-medium text-brand-300 transition hover:text-brand-200 focus-ring rounded"
                       href="{{ route('password.request') }}">
                        Esqueci minha senha
                    </a>
                @endif
            </div>
            <x-text-input id="password"
                          class="mt-1.5"
                          type="password"
                          name="password"
                          :error="$errors->has('password')"
                          required autocomplete="current-password"
                          placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        {{-- Lembrar de mim --}}
        <label for="remember_me" class="flex cursor-pointer items-center gap-2">
            <input id="remember_me" type="checkbox"
                   class="rounded border-white/20 bg-white/5 text-brand-500 focus:ring-brand-400/40"
                   name="remember">
            <span class="text-sm text-slate-300">Lembrar de mim neste dispositivo</span>
        </label>

        <x-primary-button class="w-full">
            <span wire:loading.remove>Entrar</span>
            Entrar
        </x-primary-button>
    </form>

    @if (Route::has('register'))
        <p class="mt-6 text-center text-sm text-slate-400">
            Não possui conta?
            <a href="{{ route('register') }}"
               class="font-semibold text-brand-300 transition hover:text-brand-200 focus-ring rounded">
                Criar conta
            </a>
        </p>
    @endif
</x-guest-layout>
