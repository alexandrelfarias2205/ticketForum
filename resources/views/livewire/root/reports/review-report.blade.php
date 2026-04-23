<div
    x-data="{ showRejectModal: $wire.entangle('showRejectModal') }"
    class="space-y-6"
>

    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between">
        <div>
            <a
                href="{{ route('root.reports.index') }}"
                wire:navigate
                class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors mb-2"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Fila de Revisão
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Revisar Relatório</h1>
        </div>
        <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-semibold {{ $report->status->badgeClasses() }}">
            {{ $report->status->label() }}
        </span>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- COLUNA ESQUERDA: Relatório original --}}
        <div class="space-y-4">
            <h2 class="text-base font-semibold text-gray-700 uppercase tracking-wider text-xs">Relatório Original</h2>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-4">
                {{-- Meta --}}
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $report->type->badgeClasses() }}">
                        {{ $report->type->label() }}
                    </span>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Título</p>
                    <p class="text-base font-semibold text-gray-900">{{ $report->title }}</p>
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-1">Descrição</p>
                    <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $report->description }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5">Empresa</p>
                        <p class="font-medium text-gray-800">{{ $report->tenant->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5">Autor</p>
                        <p class="font-medium text-gray-800">{{ $report->author->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-0.5">Enviado em</p>
                        <p class="text-gray-700">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                {{-- Anexos --}}
                @php
                    $images = $report->attachments->filter(fn($a) => $a->isImage());
                    $linkAttachments = $report->attachments->filter(fn($a) => $a->isLink());
                @endphp

                @if($images->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Imagens</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($images as $attachment)
                                <a
                                    href="{{ $attachment->url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="group relative block aspect-video overflow-hidden rounded-lg border border-gray-200 bg-gray-50"
                                >
                                    <img src="{{ $attachment->url }}" alt="{{ $attachment->filename }}" class="h-full w-full object-cover" loading="lazy">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all group-hover:bg-black/20">
                                        <svg class="h-6 w-6 text-white opacity-0 group-hover:opacity-100 transition-opacity" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                @if($linkAttachments->isNotEmpty())
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Links</p>
                        <ul class="space-y-1.5">
                            @foreach($linkAttachments as $attachment)
                                <li>
                                    <a
                                        href="{{ $attachment->url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 text-sm text-indigo-600 hover:text-indigo-800 transition-colors truncate max-w-full"
                                    >
                                        <svg class="h-3.5 w-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                        {{ $attachment->url }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        {{-- COLUNA DIREITA: Formulário de revisão --}}
        <div class="space-y-4">
            <h2 class="text-xs font-semibold text-gray-700 uppercase tracking-wider">Formulário de Revisão</h2>

            <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm space-y-5">

                {{-- Título enriquecido --}}
                <div>
                    <label for="enrichedTitle" class="block text-sm font-semibold text-gray-900 mb-1">
                        Título Enriquecido <span class="text-red-500">*</span>
                    </label>
                    <input
                        id="enrichedTitle"
                        type="text"
                        wire:model="enrichedTitle"
                        maxlength="500"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('enrichedTitle') border-red-400 @enderror"
                    />
                    @error('enrichedTitle')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Descrição enriquecida --}}
                <div>
                    <label for="enrichedDescription" class="block text-sm font-semibold text-gray-900 mb-1">
                        Descrição Enriquecida <span class="text-red-500">*</span>
                    </label>
                    <textarea
                        id="enrichedDescription"
                        wire:model="enrichedDescription"
                        rows="8"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 @error('enrichedDescription') border-red-400 @enderror"
                    ></textarea>
                    @error('enrichedDescription')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Labels --}}
                <div>
                    <p class="text-sm font-semibold text-gray-900 mb-2">Categorias</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->availableLabels as $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg border px-3 py-2 transition-all {{ in_array($label->id, $selectedLabels) ? 'border-indigo-400 bg-indigo-50' : 'border-gray-200 bg-white hover:border-gray-300' }}">
                                <input
                                    type="checkbox"
                                    wire:model="selectedLabels"
                                    value="{{ $label->id }}"
                                    class="sr-only"
                                >
                                <span
                                    class="h-3 w-3 rounded-full flex-shrink-0"
                                    style="background-color: {{ $label->color }};"
                                ></span>
                                <span class="text-sm font-medium text-gray-700">{{ $label->name }}</span>
                            </label>
                        @endforeach
                        @if($this->availableLabels->isEmpty())
                            <p class="text-sm text-gray-400">Nenhuma categoria cadastrada.</p>
                        @endif
                    </div>
                </div>

                {{-- Botões de ação --}}
                <div class="flex flex-wrap gap-3 border-t border-gray-100 pt-4">
                    {{-- Aprovar --}}
                    <button
                        type="button"
                        wire:click="approve"
                        wire:loading.attr="disabled"
                        wire:target="approve"
                        class="inline-flex items-center gap-2 rounded-lg bg-green-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                    >
                        <span wire:loading.remove wire:target="approve">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </span>
                        <span wire:loading wire:target="approve">
                            <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span wire:loading.remove wire:target="approve">Aprovar</span>
                        <span wire:loading wire:target="approve">Aprovando…</span>
                    </button>

                    {{-- Rejeitar --}}
                    <button
                        type="button"
                        wire:click="reject"
                        class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Rejeitar
                    </button>

                    {{-- Publicar para votação (apenas se status = approved) --}}
                    @if($report->status->canBePublished())
                        <button
                            type="button"
                            wire:click="publish"
                            wire:loading.attr="disabled"
                            wire:target="publish"
                            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                        >
                            <span wire:loading.remove wire:target="publish">
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                                </svg>
                            </span>
                            <span wire:loading wire:target="publish">
                                <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            <span wire:loading.remove wire:target="publish">Publicar para Votação</span>
                            <span wire:loading wire:target="publish">Publicando…</span>
                        </button>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- Modal de Rejeição --}}
    <div
        x-show="showRejectModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        aria-modal="true"
        role="dialog"
    >
        {{-- Overlay --}}
        <div
            class="absolute inset-0 bg-gray-900/50"
            x-on:click="showRejectModal = false"
        ></div>

        {{-- Painel --}}
        <div
            class="relative z-10 w-full max-w-md rounded-2xl bg-white p-6 shadow-xl"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100"
        >
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900">Rejeitar Relatório</h3>
                <button
                    type="button"
                    x-on:click="showRejectModal = false"
                    class="text-gray-400 hover:text-gray-600 transition-colors"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-gray-600">
                Informe o motivo da rejeição. Esta informação será registrada internamente.
            </p>

            <div class="mb-4">
                <label for="rejectReason" class="block text-sm font-semibold text-gray-900 mb-1">
                    Motivo <span class="text-red-500">*</span>
                </label>
                <textarea
                    id="rejectReason"
                    wire:model="rejectReason"
                    rows="4"
                    placeholder="Descreva o motivo da rejeição…"
                    class="w-full rounded-lg border border-gray-300 px-4 py-2.5 text-sm shadow-sm placeholder-gray-400 focus:border-red-400 focus:outline-none focus:ring-1 focus:ring-red-400 @error('rejectReason') border-red-400 @enderror"
                ></textarea>
                @error('rejectReason')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button
                    type="button"
                    x-on:click="showRejectModal = false"
                    class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    wire:click="confirmReject"
                    wire:loading.attr="disabled"
                    wire:target="confirmReject"
                    class="inline-flex items-center gap-2 rounded-lg bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors disabled:opacity-60 disabled:cursor-not-allowed"
                >
                    <span wire:loading wire:target="confirmReject">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </span>
                    <span wire:loading.remove wire:target="confirmReject">Confirmar Rejeição</span>
                    <span wire:loading wire:target="confirmReject">Rejeitando…</span>
                </button>
            </div>
        </div>
    </div>

</div>
