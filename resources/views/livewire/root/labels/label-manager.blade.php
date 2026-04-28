<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Etiquetas</h1>
            <p class="page-subtitle">Gerencie as etiquetas usadas para categorizar tickets.</p>
        </div>
        <button wire:click="openCreate" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova etiqueta
        </button>
    </div>

    {{-- Form inline --}}
    @if($showForm)
        <div class="card mb-6">
            <h2 class="section-title mb-4">{{ $editingId ? 'Editar etiqueta' : 'Nova etiqueta' }}</h2>

            <div class="flex flex-col gap-4 sm:flex-row sm:items-end">
                <div class="flex-1">
                    <label for="label-name" class="label-dark mb-1">Nome</label>
                    <input id="label-name" wire:model="name" type="text" maxlength="100"
                           placeholder="Ex.: Bug crítico"
                           class="input-dark @error('name') input-dark-error @enderror" />
                    @error('name') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="label-dark mb-1">Cor</label>
                    <div class="flex items-center gap-2" x-data="{ color: @entangle('color') }">
                        <input type="color" x-model="color"
                               @input="$wire.set('color', $event.target.value)"
                               class="h-10 w-12 cursor-pointer rounded-lg border border-white/15 bg-white/5 p-0.5" />
                        <input type="text" x-model="color"
                               @input="$wire.set('color', $event.target.value)"
                               maxlength="7" placeholder="#6366f1"
                               class="input-dark w-32 font-mono @error('color') input-dark-error @enderror" />
                    </div>
                    @error('color') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">
                        Salvar
                    </button>
                    <button wire:click="resetForm" class="btn-secondary">
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Cor</th>
                        <th>Nome</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labels as $label)
                        <tr class="table-row">
                            <td>
                                <span class="inline-block h-5 w-5 rounded-full ring-1 ring-white/20"
                                      style="background-color: {{ $label->color }};"
                                      title="{{ $label->color }}"></span>
                            </td>
                            <td class="font-medium text-white">{{ $label->name }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-secondary-button wire:click="openEdit('{{ $label->id }}')">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        Editar
                                    </x-secondary-button>
                                    <x-danger-button
                                        wire:click="delete('{{ $label->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="delete('{{ $label->id }}')"
                                        x-on:click="confirm('Tem certeza que deseja excluir esta etiqueta? Ela será desvinculada de todos os tickets.') || $event.stopImmediatePropagation()"
                                    >
                                        <svg wire:loading.remove wire:target="delete('{{ $label->id }}')" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                        </svg>
                                        <svg wire:loading wire:target="delete('{{ $label->id }}')" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                        </svg>
                                        Excluir
                                    </x-danger-button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-14 w-14 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                <h3 class="font-semibold text-slate-200">Nenhuma etiqueta criada</h3>
                                <p class="mt-1 text-sm text-slate-500">Crie etiquetas para categorizar e organizar os tickets.</p>
                                <div class="mt-4">
                                    <x-primary-button wire:click="openCreate">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Nova etiqueta
                                    </x-primary-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
