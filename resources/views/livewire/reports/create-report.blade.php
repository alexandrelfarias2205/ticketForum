<div class="max-w-3xl mx-auto space-y-8">
    {{-- Cabeçalho --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Novo Relatório</h1>
        <p class="mt-1 text-sm text-gray-500">Descreva o problema ou sugestão com o máximo de detalhes possível.</p>
    </div>

    <form wire:submit="save" class="space-y-6">

        {{-- Produto --}}
        @if ($this->products->isNotEmpty())
        <div>
            <label for="productId" class="text-sm font-semibold text-gray-900">
                Produto <span class="text-red-500">*</span>
            </label>
            <select
                id="productId"
                wire:model="productId"
                class="mt-1.5 w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('productId') border-red-400 @enderror"
            >
                <option value="">Selecione o produto...</option>
                @foreach ($this->products as $product)
                    <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
            @error('productId')
                <span class="text-red-600 text-xs mt-1">{{ $message }}</span>
            @enderror
        </div>
        @endif

        {{-- Tipo do Relatório --}}
        <div>
            <fieldset>
                <legend class="text-sm font-semibold text-gray-900 mb-3">Tipo de Relatório <span class="text-red-500">*</span></legend>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">

                    {{-- Bug --}}
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-4 transition-all {{ $type === 'bug' ? 'border-red-500 bg-red-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                        <input type="radio" wire:model="type" value="bug" class="sr-only">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 {{ $type === 'bug' ? 'text-red-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 12.75c1.148 0 2.278.08 3.383.237 1.037.146 1.86.966 1.86 2.013 0 3.728-2.35 6.75-5.243 6.75S6.75 18.728 6.75 15c0-1.046.82-1.867 1.857-2.013A24.204 24.204 0 0112 12.75zm0 0c2.883 0 5.647.508 8.207 1.44a23.91 23.91 0 01-.62 3.478l-1.07 3.213a.75.75 0 01-.71.524H6.193a.75.75 0 01-.71-.524l-1.07-3.213A23.907 23.907 0 013.793 14.19 24.19 24.19 0 0112 12.75z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.169.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 01-6.23-.693L4.2 15.3m15.6 0l-1.897 6.623A1.875 1.875 0 0116.11 23H7.89a1.875 1.875 0 01-1.793-1.077L4.2 15.3" />
                                </svg>
                                <span class="font-semibold text-sm {{ $type === 'bug' ? 'text-red-700' : 'text-gray-700' }}">Bug</span>
                            </div>
                            <p class="text-xs text-gray-500">Algo está funcionando de forma incorreta ou quebrando.</p>
                        </div>
                    </label>

                    {{-- Melhoria --}}
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-4 transition-all {{ $type === 'improvement' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                        <input type="radio" wire:model="type" value="improvement" class="sr-only">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 {{ $type === 'improvement' ? 'text-blue-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18L9 11.25l4.306 4.307a11.95 11.95 0 015.814-5.519l2.74-1.22m0 0l-5.94-2.28m5.94 2.28l-2.28 5.941" />
                                </svg>
                                <span class="font-semibold text-sm {{ $type === 'improvement' ? 'text-blue-700' : 'text-gray-700' }}">Melhoria</span>
                            </div>
                            <p class="text-xs text-gray-500">Uma funcionalidade existente pode ser aprimorada.</p>
                        </div>
                    </label>

                    {{-- Nova Funcionalidade --}}
                    <label class="relative flex cursor-pointer rounded-xl border-2 p-4 transition-all {{ $type === 'feature_request' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                        <input type="radio" wire:model="type" value="feature_request" class="sr-only">
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center gap-2">
                                <svg class="h-5 w-5 {{ $type === 'feature_request' ? 'text-indigo-500' : 'text-gray-400' }}" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                                <span class="font-semibold text-sm {{ $type === 'feature_request' ? 'text-indigo-700' : 'text-gray-700' }}">Nova Funcionalidade</span>
                            </div>
                            <p class="text-xs text-gray-500">Solicite uma nova funcionalidade para o sistema.</p>
                        </div>
                    </label>

                </div>
            </fieldset>
            @error('type')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Título --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="title" class="text-sm font-semibold text-gray-900">Título <span class="text-red-500">*</span></label>
                <span class="text-xs text-gray-400">{{ strlen($title) }}/500</span>
            </div>
            <input
                id="title"
                type="text"
                wire:model.live="title"
                maxlength="500"
                placeholder="Descreva o problema ou solicitação em uma frase"
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('title') border-red-400 @enderror"
            />
            @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Descrição --}}
        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="description" class="text-sm font-semibold text-gray-900">Descrição <span class="text-red-500">*</span></label>
                <span class="text-xs text-gray-400">{{ strlen($description) }} caracteres</span>
            </div>
            <textarea
                id="description"
                wire:model.live="description"
                rows="6"
                placeholder="Descreva com detalhes: o que aconteceu, o que esperava que acontecesse, como reproduzir o problema..."
                class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('description') border-red-400 @enderror"
            ></textarea>
            @error('description')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Links --}}
        <div>
            <label class="text-sm font-semibold text-gray-900">Links relacionados</label>
            <p class="mt-0.5 text-xs text-gray-500 mb-3">Adicione URLs de referência, prints hospedados ou documentação relevante.</p>

            {{-- Input para novo link --}}
            <div class="flex gap-2">
                <input
                    type="url"
                    wire:model="newLink"
                    wire:keydown.enter.prevent="addLink"
                    placeholder="https://exemplo.com"
                    class="flex-1 rounded-lg border border-gray-300 px-4 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
                />
                <button
                    type="button"
                    wire:click="addLink"
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Adicionar Link
                </button>
            </div>

            {{-- Lista de links adicionados --}}
            @if(count($links) > 0)
                <ul class="mt-3 space-y-2">
                    @foreach($links as $index => $link)
                        <li class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2">
                            <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
                            </svg>
                            <span class="flex-1 truncate text-sm text-gray-700">{{ $link }}</span>
                            <button
                                type="button"
                                wire:click="removeLink({{ $index }})"
                                class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors"
                                title="Remover link"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- Aviso sobre upload de imagens --}}
        <div class="rounded-xl border border-amber-200 bg-amber-50 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-amber-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
                </svg>
                <div>
                    <p class="text-sm font-medium text-amber-800">Upload de imagens</p>
                    <p class="mt-0.5 text-sm text-amber-700">Após enviar o relatório, você poderá adicionar imagens na página de detalhes.</p>
                </div>
            </div>
        </div>

        {{-- Botão de envio --}}
        <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
            <a
                href="{{ route('app.reports.index') }}"
                wire:navigate
                class="rounded-lg border border-gray-300 bg-white px-5 py-2.5 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
            >
                Cancelar
            </a>
            <button
                type="submit"
                wire:loading.attr="disabled"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
            >
                <span wire:loading.remove wire:target="save">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                    </svg>
                </span>
                <span wire:loading wire:target="save">
                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
                <span wire:loading.remove wire:target="save">Enviar Relatório</span>
                <span wire:loading wire:target="save">Enviando…</span>
            </button>
        </div>

    </form>
</div>
