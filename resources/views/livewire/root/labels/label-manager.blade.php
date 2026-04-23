<div>
    {{-- Page header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Etiquetas</h1>
            <p class="mt-1 text-sm text-gray-500">Gerencie as etiquetas usadas para categorizar relatórios.</p>
        </div>
        <button
            wire:click="openCreate"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova Etiqueta
        </button>
    </div>

    {{-- Inline form --}}
    @if($showForm)
        <div class="mb-6 rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="mb-4 text-base font-semibold text-gray-800">
                {{ $editingId ? 'Editar Etiqueta' : 'Nova Etiqueta' }}
            </h2>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                {{-- Name --}}
                <div class="flex-1">
                    <label for="label-name" class="block text-sm font-medium text-gray-700 mb-1">Nome</label>
                    <input
                        id="label-name"
                        wire:model="name"
                        type="text"
                        maxlength="100"
                        placeholder="Ex: Bug crítico"
                        class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('name') border-red-500 @enderror"
                    />
                    @error('name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Color --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cor</label>
                    <div class="flex items-center gap-2"
                         x-data="{ color: @entangle('color') }">
                        <input
                            type="color"
                            x-model="color"
                            @input="$wire.set('color', $event.target.value)"
                            class="h-9 w-12 cursor-pointer rounded border border-gray-300 p-0.5"
                        />
                        <input
                            type="text"
                            x-model="color"
                            @input="$wire.set('color', $event.target.value)"
                            maxlength="7"
                            placeholder="#6366f1"
                            class="w-28 rounded-lg border border-gray-300 px-3 py-2 text-sm font-mono shadow-sm focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('color') border-red-500 @enderror"
                        />
                    </div>
                    @error('color')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-2">
                    <button
                        wire:click="save"
                        wire:loading.attr="disabled"
                        class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-50">
                        Salvar
                    </button>
                    <button
                        wire:click="resetForm"
                        class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 transition-colors">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Labels table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Cor</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Nome</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-600 uppercase tracking-wider text-xs">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($labels as $label)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span
                                class="inline-block h-5 w-5 rounded-full border border-gray-200 shadow-sm"
                                style="background-color: {{ $label->color }};"
                                title="{{ $label->color }}"
                            ></span>
                        </td>
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $label->name }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <button
                                    wire:click="openEdit('{{ $label->id }}')"
                                    class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors">
                                    Editar
                                </button>
                                <button
                                    wire:click="delete('{{ $label->id }}')"
                                    x-on:click="confirm('Tem certeza que deseja excluir esta etiqueta? Ela será desvinculada de todos os relatórios.') || $event.stopImmediatePropagation()"
                                    class="text-red-600 hover:text-red-900 font-medium transition-colors">
                                    Excluir
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-6 py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                            <p class="font-medium">Nenhuma etiqueta criada.</p>
                            <p class="mt-1 text-sm">Clique em "Nova Etiqueta" para começar.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
