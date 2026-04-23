<div class="mx-auto max-w-2xl">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('root.tenants.index') }}" class="text-gray-400 hover:text-gray-600 transition-colors">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Editar Empresa</h1>
            <p class="mt-0.5 text-sm text-gray-500">{{ $tenant->name }}</p>
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
                    autocomplete="organization"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('name') border-red-500 focus:border-red-500 focus:ring-red-500 @enderror"
                />
                @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Plano --}}
            <div>
                <label for="plan" class="block text-sm font-medium text-gray-700">
                    Plano <span class="text-red-500">*</span>
                </label>
                <select
                    wire:model="plan"
                    id="plan"
                    class="mt-1 block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('plan') border-red-500 @enderror"
                >
                    <option value="free">Gratuito</option>
                    <option value="pro">Pro</option>
                    <option value="enterprise">Enterprise</option>
                </select>
                @error('plan')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status --}}
            <div>
                <div class="flex items-center justify-between">
                    <div>
                        <span class="block text-sm font-medium text-gray-700">Status</span>
                        <span class="text-xs text-gray-500">Desativar bloqueará todos os usuários da empresa.</span>
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

            {{-- Actions --}}
            <div class="flex items-center justify-end gap-3 pt-2">
                <a href="{{ route('root.tenants.index') }}"
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
