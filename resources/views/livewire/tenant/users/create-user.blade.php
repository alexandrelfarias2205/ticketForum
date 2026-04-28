<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="page-title">Novo usuário</h1>
        <p class="page-subtitle">Preencha os dados para cadastrar um novo usuário na sua empresa.</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        <div>
            <label for="name" class="label-dark">Nome <span class="text-danger-400">*</span></label>
            <input wire:model="name" id="name" type="text" autocomplete="name"
                   placeholder="Nome completo"
                   class="input-dark mt-1.5 @error('name') input-dark-error @enderror" />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label-dark">E-mail <span class="text-danger-400">*</span></label>
            <input wire:model="email" id="email" type="email" autocomplete="email"
                   placeholder="usuario@exemplo.com"
                   class="input-dark mt-1.5 @error('email') input-dark-error @enderror" />
            @error('email') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="role" class="label-dark">Papel <span class="text-danger-400">*</span></label>
            <select wire:model="role" id="role"
                    class="input-dark mt-1.5 @error('role') input-dark-error @enderror">
                <option value="tenant_user">Usuário</option>
                <option value="tenant_admin">Administrador</option>
            </select>
            @error('role') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password" class="label-dark">Senha <span class="text-danger-400">*</span></label>
            <input wire:model="password" id="password" type="password" autocomplete="new-password"
                   placeholder="Mínimo 12 caracteres"
                   class="input-dark mt-1.5 @error('password') input-dark-error @enderror" />
            @error('password') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="password_confirmation" class="label-dark">Confirmar senha <span class="text-danger-400">*</span></label>
            <input wire:model="password_confirmation" id="password_confirmation" type="password"
                   autocomplete="new-password" placeholder="Repita a senha"
                   class="input-dark mt-1.5" />
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('app.admin.users.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Salvar</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </button>
        </div>
    </form>
</div>
