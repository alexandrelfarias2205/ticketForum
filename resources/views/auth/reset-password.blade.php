<x-guest-layout>
    <div class="mb-6">
        <h2 class="text-h2 text-white">Definir nova senha</h2>
        <p class="mt-1 text-sm text-slate-400">Escolha uma senha forte que você lembre.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div>
            <x-input-label for="email" value="E-mail" />
            <x-text-input id="email" class="mt-1.5" type="email" name="email"
                          :value="old('email', $request->email)" :error="$errors->has('email')"
                          required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" />
        </div>

        <div>
            <x-input-label for="password" value="Nova senha" />
            <x-text-input id="password" class="mt-1.5" type="password" name="password"
                          :error="$errors->has('password')"
                          required autocomplete="new-password"
                          placeholder="Mínimo 8 caracteres" />
            <x-input-error :messages="$errors->get('password')" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Confirme a nova senha" />
            <x-text-input id="password_confirmation" class="mt-1.5" type="password" name="password_confirmation"
                          :error="$errors->has('password_confirmation')"
                          required autocomplete="new-password" />
            <x-input-error :messages="$errors->get('password_confirmation')" />
        </div>

        <x-primary-button class="w-full">
            Redefinir senha
        </x-primary-button>
    </form>
</x-guest-layout>
