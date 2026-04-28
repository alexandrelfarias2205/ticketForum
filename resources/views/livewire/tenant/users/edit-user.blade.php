<div class="mx-auto max-w-2xl">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('app.admin.users.index') }}" class="text-slate-400 transition hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="page-title">Editar usuário</h1>
            <p class="page-subtitle">{{ $user->email }}</p>
        </div>
    </div>

    <form wire:submit="save" class="card space-y-6">

        <div>
            <label for="name" class="label-dark">Nome <span class="text-danger-400">*</span></label>
            <input wire:model="name" id="name" type="text" autocomplete="name"
                   class="input-dark mt-1.5 @error('name') input-dark-error @enderror" />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="email" class="label-dark">E-mail <span class="text-danger-400">*</span></label>
            <input wire:model="email" id="email" type="email" autocomplete="email"
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

        {{-- Status toggle --}}
        <div>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span class="label-dark">Status</span>
                    <p class="help-text">Usuários inativos não conseguem fazer login.</p>
                </div>
                <button
                    type="button"
                    wire:click="$toggle('is_active')"
                    :class="$wire.is_active ? 'bg-gradient-brand' : 'bg-white/10'"
                    class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-ring"
                    role="switch"
                >
                    <span
                        :class="$wire.is_active ? 'translate-x-5' : 'translate-x-0'"
                        class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"
                    ></span>
                </button>
            </div>
            <p class="mt-1 text-xs text-slate-400">
                Situação atual:
                <span class="font-medium" :class="$wire.is_active ? 'text-success-400' : 'text-danger-400'"
                      x-text="$wire.is_active ? 'Ativo' : 'Inativo'"></span>
            </p>
        </div>

        {{-- Alterar senha --}}
        <div class="rounded-xl border border-white/10 bg-white/[0.03] p-4">
            <p class="mb-4 text-xs font-semibold uppercase tracking-wider text-slate-400">Alterar senha (opcional)</p>
            <div class="space-y-4">
                <div>
                    <label for="password" class="label-dark">Nova senha</label>
                    <input wire:model="password" id="password" type="password" autocomplete="new-password"
                           placeholder="Deixe em branco para não alterar"
                           class="input-dark mt-1.5 @error('password') input-dark-error @enderror" />
                    @error('password') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="password_confirmation" class="label-dark">Confirmar nova senha</label>
                    <input wire:model="password_confirmation" id="password_confirmation" type="password"
                           autocomplete="new-password" placeholder="Repita a nova senha"
                           class="input-dark mt-1.5" />
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('app.admin.users.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </button>
        </div>
    </form>
</div>
