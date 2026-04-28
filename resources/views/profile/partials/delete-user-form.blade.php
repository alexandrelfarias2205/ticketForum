<section class="space-y-6">
    <header>
        <h2 class="section-title text-danger-400">Excluir conta</h2>
        <p class="mt-1 text-sm text-slate-400">
            Ao excluir sua conta, todos os seus dados serão permanentemente removidos.
            Antes de prosseguir, faça o download das informações que deseja preservar.
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >
        Excluir conta
    </x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h3 class="text-h3 text-white">
                Tem certeza que deseja excluir sua conta?
            </h3>

            <p class="mt-2 text-sm text-slate-400">
                Ao excluir sua conta, todos os seus dados serão permanentemente removidos.
                Informe sua senha para confirmar a exclusão.
            </p>

            <div class="mt-6">
                <x-input-label for="password" value="Senha" class="sr-only" />
                <x-text-input id="password" name="password" type="password"
                              class="block w-3/4"
                              :error="$errors->userDeletion->has('password')"
                              placeholder="Sua senha" />
                <x-input-error :messages="$errors->userDeletion->get('password')" />
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <x-secondary-button x-on:click="$dispatch('close')">Cancelar</x-secondary-button>
                <x-danger-button>Excluir conta</x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
