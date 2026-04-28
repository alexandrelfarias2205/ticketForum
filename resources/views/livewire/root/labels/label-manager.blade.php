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
                <thead>
                    <tr>
                        <th>Cor</th>
                        <th>Nome</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($labels as $label)
                        <tr>
                            <td>
                                <span class="inline-block h-5 w-5 rounded-full ring-1 ring-white/20"
                                      style="background-color: {{ $label->color }};"
                                      title="{{ $label->color }}"></span>
                            </td>
                            <td class="font-medium text-white">{{ $label->name }}</td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <button wire:click="openEdit('{{ $label->id }}')"
                                            class="text-sm font-medium text-brand-300 transition hover:text-brand-200">
                                        Editar
                                    </button>
                                    <button wire:click="delete('{{ $label->id }}')"
                                            x-on:click="confirm('Tem certeza que deseja excluir esta etiqueta? Ela será desvinculada de todos os tickets.') || $event.stopImmediatePropagation()"
                                            class="text-sm font-medium text-danger-400 transition hover:text-danger-300">
                                        Excluir
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                <p class="font-medium text-slate-300">Nenhuma etiqueta criada.</p>
                                <p class="mt-1 text-sm text-slate-500">Clique em "Nova etiqueta" para começar.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
