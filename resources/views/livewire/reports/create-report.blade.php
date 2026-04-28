<div class="mx-auto max-w-3xl space-y-6">
    {{-- Cabeçalho --}}
    <div>
        <h1 class="page-title">Novo ticket</h1>
        <p class="page-subtitle">Descreva o problema ou sugestão com o máximo de detalhes possível.</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        {{-- Produto --}}
        @if ($this->products->isNotEmpty())
        <div>
            <label for="productId" class="label-dark">
                Produto <span class="text-danger-400">*</span>
            </label>
            <select
                id="productId"
                wire:model="productId"
                class="input-dark mt-1.5 @error('productId') input-dark-error @enderror"
            >
                <option value="">Selecione o produto...</option>
                @foreach ($this->products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            @error('productId')
                <p class="error-text mt-1">{{ $message }}</p>
            @enderror
        </div>
        @endif

        {{-- Tipo --}}
        <fieldset>
            <legend class="label-dark mb-3">
                Tipo de ticket <span class="text-danger-400">*</span>
            </legend>
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
                            <span class="font-semibold text-sm {{ $active ? $textClass : 'text-slate-200' }}">
                                {{ $opt['label'] }}
                            </span>
                            <p class="text-xs text-slate-400">{{ $opt['desc'] }}</p>
                        </div>
                    </label>
                @endforeach
            </div>
            @error('type')
                <p class="error-text mt-2">{{ $message }}</p>
            @enderror
        </fieldset>

        {{-- Título --}}
        <div>
            <div class="mb-1 flex items-center justify-between">
                <label for="title" class="label-dark">Título <span class="text-danger-400">*</span></label>
                <span class="text-xs text-slate-500">{{ strlen($title) }}/500</span>
            </div>
            <input
                id="title"
                type="text"
                wire:model.live="title"
                maxlength="500"
                placeholder="Descreva o problema ou solicitação em uma frase"
                class="input-dark @error('title') input-dark-error @enderror"
            />
            @error('title')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        {{-- Descrição --}}
        <div>
            <div class="mb-1 flex items-center justify-between">
                <label for="description" class="label-dark">Descrição <span class="text-danger-400">*</span></label>
                <span class="text-xs text-slate-500">{{ strlen($description) }} caracteres</span>
            </div>
            <textarea
                id="description"
                wire:model.live="description"
                rows="6"
                placeholder="Descreva com detalhes: o que aconteceu, o que esperava, como reproduzir..."
                class="input-dark @error('description') input-dark-error @enderror"
            ></textarea>
            @error('description')
                <p class="error-text">{{ $message }}</p>
            @enderror
        </div>

        {{-- Links --}}
        <div>
            <label class="label-dark">Links relacionados</label>
            <p class="help-text mb-3">Adicione URLs de referência, prints hospedados ou documentação relevante.</p>

            <div class="flex gap-2">
                <input
                    type="url"
                    wire:model="newLink"
                    wire:keydown.enter.prevent="addLink"
                    placeholder="https://exemplo.com"
                    class="input-dark flex-1"
                />
                <button type="button" wire:click="addLink" class="btn-secondary shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Adicionar
                </button>
            </div>

            @if(count($links) > 0)
                <ul class="mt-3 space-y-2">
                    @foreach($links as $index => $link)
                        <li class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            <span class="flex-1 truncate text-sm text-slate-200">{{ $link }}</span>
                            <button type="button"
                                    wire:click="removeLink({{ $index }})"
                                    class="shrink-0 text-slate-500 transition hover:text-danger-400"
                                    title="Remover link">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Aviso anexos --}}
        <div class="flex gap-3 rounded-xl border border-warning-400/30 bg-warning-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div>
                <p class="text-sm font-medium text-warning-300">Upload de imagens</p>
                <p class="mt-0.5 text-xs text-warning-200/80">Após enviar o ticket, você poderá adicionar imagens na página de detalhes.</p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('app.reports.index') }}" wire:navigate class="btn-secondary">
                Cancelar
            </a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove wire:target="save">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </span>
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Enviar ticket</span>
                <span wire:loading wire:target="save">Enviando…</span>
            </button>
        </div>
    </form>
</div>
