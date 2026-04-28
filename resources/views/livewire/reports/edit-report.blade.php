<div class="mx-auto max-w-3xl space-y-6">
    <div>
        <h1 class="page-title">Editar ticket</h1>
        <p class="page-subtitle">Você pode editar este ticket enquanto ele aguarda revisão.</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        {{-- Tipo --}}
        <fieldset>
            <legend class="label-dark mb-3">Tipo de ticket <span class="text-danger-400">*</span></legend>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                @php
                    $typeOptions = [
                        ['value' => 'bug', 'label' => 'Bug', 'tone' => 'danger', 'desc' => 'Algo está funcionando de forma incorreta.'],
                        ['value' => 'improvement', 'label' => 'Melhoria', 'tone' => 'info', 'desc' => 'Uma funcionalidade existente pode ser aprimorada.'],
                        ['value' => 'feature_request', 'label' => 'Nova Funcionalidade', 'tone' => 'brand', 'desc' => 'Solicite uma nova funcionalidade.'],
                    ];
                @endphp

                @foreach ($typeOptions as $opt)
                    @php
                        $active = $type === $opt['value'];
                        $ringClass = match($opt['tone']) {
                            'danger' => 'ring-danger-400/60 bg-danger-500/10',
                            'info'   => 'ring-info-400/60 bg-info-500/10',
                            default  => 'ring-brand-400/60 bg-brand-500/10',
                        };
                        $textClass = match($opt['tone']) {
                            'danger' => 'text-danger-300',
                            'info'   => 'text-info-300',
                            default  => 'text-brand-300',
                        };
                    @endphp
                    <label class="relative flex cursor-pointer rounded-xl p-4 ring-1 ring-inset transition-all
                                  {{ $active ? $ringClass : 'ring-white/10 bg-white/[0.03] hover:ring-white/25 hover:bg-white/[0.06]' }}">
                        <input type="radio" wire:model="type" value="{{ $opt['value'] }}" class="sr-only">
                        <div class="flex flex-col gap-2">
                            <span class="font-semibold text-sm {{ $active ? $textClass : 'text-slate-200' }}">{{ $opt['label'] }}</span>
                            <p class="text-xs text-slate-400">{{ $opt['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('type') <p class="error-text mt-2">{{ $message }}</p> @enderror
        </fieldset>

        {{-- Título --}}
        <div>
            <div class="mb-1 flex items-center justify-between">
                <label for="title" class="label-dark">Título <span class="text-danger-400">*</span></label>
                <span class="text-xs text-slate-500">{{ strlen($title) }}/500</span>
            </div>
            <input id="title" type="text" wire:model.live="title" maxlength="500"
                   class="input-dark @error('title') input-dark-error @enderror"
                   placeholder="Descreva o problema ou solicitação em uma frase" />
            @error('title') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        {{-- Descrição --}}
        <div>
            <div class="mb-1 flex items-center justify-between">
                <label for="description" class="label-dark">Descrição <span class="text-danger-400">*</span></label>
                <span class="text-xs text-slate-500">{{ strlen($description) }} caracteres</span>
            </div>
            <textarea id="description" wire:model.live="description" rows="6"
                      class="input-dark @error('description') input-dark-error @enderror"
                      placeholder="Descreva com detalhes..."></textarea>
            @error('description') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('app.reports.show', $report) }}" wire:navigate class="btn-secondary">
                Cancelar
            </a>
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
