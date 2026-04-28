<section>
    <header>
        <h2 class="section-title">Atualizar senha</h2>
        <p class="mt-1 text-sm text-slate-400">
            Use uma senha longa e aleatória para manter sua conta segura.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" value="Senha atual" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                          class="mt-1.5"
                          :error="$errors->updatePassword->has('current_password')"
                          autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" />
        </div>

        <div>
            <x-input-label for="update_password_password" value="Nova senha" />
            <x-text-input id="update_password_password" name="password" type="password"
                          class="mt-1.5"
                          :error="$errors->updatePassword->has('password')"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" value="Confirmar nova senha" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                          class="mt-1.5"
                          :error="$errors->updatePassword->has('password_confirmation')"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Salvar</x-primary-button>

            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition
                   x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-success-400">
                    Salvo.
                </p>
            @endif
        </div>
    </form>
</section>
