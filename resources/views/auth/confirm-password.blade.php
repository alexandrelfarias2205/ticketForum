<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Confirme sua senha</h2>
        <p class="mt-2 text-sm text-slate-400">
            Esta é uma área protegida. Por favor, confirme sua senha antes de continuar.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-5">
        @csrf

        <div>
            <x-input-label for="password" value="Senha" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password"
                          :error="$errors->has('password')"
                          required autocomplete="current-password"
                          placeholder="Sua senha atual" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <x-primary-button class="w-full">
            Confirmar
        </x-primary-button>
    </form>
</x-guest-layout>
