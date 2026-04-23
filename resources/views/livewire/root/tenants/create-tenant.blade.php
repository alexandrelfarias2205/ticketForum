<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Nova Empresa</h1>
        <p class="mt-1 text-sm text-gray-500">Preencha os dados para cadastrar uma nova empresa.</p>
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
                    placeholder="Ex.: Acme Corp"
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
                    <span wire:loading.remove>Salvar</span>
                    <span wire:loading>Salvando…</span>
                </button>
            </div>
        </form>
    </div>
</div>
