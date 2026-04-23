<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Novo Usuário</h1>
        <p class="mt-1 text-sm text-gray-500">Preencha os dados para cadastrar um novo usuário na sua empresa.</p>
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
                    placeholder="Nome completo"
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
                    placeholder="usuario@exemplo.com"
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
                    wire:model="role"
                    id="role"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('role') border-red-500 @enderror"
                >
                    <option value="tenant_user">Usuário</option>
                    <option value="tenant_admin">Administrador</option>
                </select>
                @error('role')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Senha --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">
                    Senha <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="password"
                    id="password"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Mínimo 12 caracteres"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('password') border-red-500 @enderror"
                />
                @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Confirmar senha --}}
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                    Confirmar Senha <span class="text-red-500">*</span>
                </label>
                <input
                    wire:model="password_confirmation"
                    id="password_confirmation"
                    type="password"
                    autocomplete="new-password"
                    placeholder="Repita a senha"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('app.admin.users.index') }}"
                   class="rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button
                    type="submit"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-60 disabled:cursor-not-allowed transition-colors">
                    <span wire:loading.remove>Salvar</span>
                    <span wire:loading>Salvando…</span>
                </button>
            </div>
        </form>
    </div>
</div>
