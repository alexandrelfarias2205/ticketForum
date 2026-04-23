<div class="mx-auto max-w-2xl">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('root.users.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Usuário</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ $user->email }}</p>
        </div>
    </div>

    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <form wire:submit="save" class="space-y-6">

            {{-- Nome --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">
                    Nome <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="name"
                    id="name"
                    type="text"
                    autocomplete="name"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- E-mail --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">
                    E-mail <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="email"
                    id="email"
                    type="email"
                    autocomplete="email"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('email') border-red-500 @enderror"
                />
                @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Papel --}}
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700">
                    Papel <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model.live="role"
                    id="role"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('role') border-red-500 @enderror"
                >
                    <option value="tenant_user">Usuário</option>
                    <option value="tenant_admin">Administrador</option>
                    <option value="root">Administrador Root</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Empresa (only shown when role != root) --}}
            @if($role !== 'root')
            <div>
                <label for="tenant_id" class="block text-sm font-medium text-gray-700">
                    Empresa <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model="tenant_id"
                    id="tenant_id"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('tenant_id') border-red-500 @enderror"
                >
                    <option value="">Selecione uma empresa…</option>
                    @foreach($this->tenants as $tenant)
                        <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                    @endforeach
                </select>
                @error('tenant_id')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            @endif

            {{-- Status --}}
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-sm font-medium text-gray-700">Status</span>
                        <span class="text-xs text-gray-500">Usuários inativos não conseguem fazer login.</span>
                    </div>
                    <button
                        type="button"
                        wire:click="$toggle('is_active')"
                        :class="$wire.is_active ? 'bg-indigo-600' : 'bg-gray-200'"
                        class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                        role="switch"
                    >
                        <span
                            :class="$wire.is_active ? 'translate-x-5' : 'translate-x-0'"
                            class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                        ></span>
                    </button>
                </div>
                <p class="mt-1 text-xs text-gray-500">
                    Situação atual: <span class="font-medium" :class="$wire.is_active ? 'text-green-600' : 'text-red-600'" x-text="$wire.is_active ? 'Ativo' : 'Inativo'"></span>
                </p>
            </div>

            {{-- Senha (opcional) --}}
            <div class="rounded-lg bg-gray-50 p-4">
                <p class="mb-4 text-xs font-medium text-gray-500 uppercase tracking-wider">Alterar Senha (opcional)</p>
                <div class="space-y-4">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">Nova Senha</label>
                        <input
                            wire:model="password"
                            id="password"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Deixe em branco para não alterar"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
                        />
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar Nova Senha</label>
                        <input
                            wire:model="password_confirmation"
                            id="password_confirmation"
                            type="password"
                            autocomplete="new-password"
                            placeholder="Repita a nova senha"
                            class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                        />
                    </div>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('root.users.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                    <span wire:loading.remove>Salvar Alterações</span>
                    <span wire:loading>Salvando…</span>
                </button>
            </div>
        </form>
    </div>
</div>
