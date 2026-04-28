<div
    x-data="{ showRejectModal: $wire.entangle('showRejectModal') }"
    class="space-y-6"
>
    {{-- Cabeçalho --}}
    <div class="flex items-center justify-between gap-4">
        <div>
            <a href="{{ route('root.reports.index') }}" wire:navigate
               class="mb-2 inline-flex items-center gap-1.5 text-sm text-slate-400 transition hover:text-slate-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                </svg>
                Fila de revisão
            </a>
            <h1 class="page-title">Revisar ticket</h1>
        </div>
        <x-badge :status="$report->status" />
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">

        {{-- Coluna esquerda: original --}}
        <div class="space-y-3">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Ticket original</h2>

            <div class="card space-y-4">
                <div class="flex flex-wrap gap-2">
                    <x-badge :status="$report->type" />
                </div>

                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Título</p>
                    <p class="text-base font-semibold text-white">{{ $report->title }}</p>
                </div>

                <div>
                    <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-slate-400">Descrição</p>
                    <div class="whitespace-pre-wrap text-sm text-slate-200">{{ $report->description }}</div>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Empresa</p>
                        <p class="font-medium text-white">{{ $report->tenant->name }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Autor</p>
                        <p class="font-medium text-white">{{ $report->author->name }}</p>
                    </div>
                    <div>
                        <p class="mb-0.5 text-xs font-semibold uppercase tracking-wider text-slate-400">Enviado em</p>
                        <p class="text-slate-300">{{ $report->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                </div>

                @php
                    $images = $report->attachments->filter(fn($a) => $a->isImage());
                    $linkAttachments = $report->attachments->filter(fn($a) => $a->isLink());
                @endphp

                @if($images->isNotEmpty())
                    <div>
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Imagens</p>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($images as $attachment)
                                <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                                   class="group relative block aspect-video overflow-hidden rounded-lg border border-white/10 bg-white/[0.03]">
                                    <img src="{{ $attachment->url }}" alt="{{ $attachment->filename }}"
                                         class="h-full w-full object-cover" loading="lazy">
                                    <div class="absolute inset-0 flex items-center justify-center bg-black/0 transition-all group-hover:bg-black/40">
                                        <svg class="h-6 w-6 text-white opacity-0 transition-opacity group-hover:opacity-100" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
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
                        <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Links</p>
                        <ul class="space-y-1.5">
                            @foreach($linkAttachments as $attachment)
                                <li>
                                    <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                                       class="inline-flex max-w-full items-center gap-1.5 truncate text-sm text-brand-300 transition hover:text-brand-200">
                                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
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

        {{-- Coluna direita: revisão --}}
        <div class="space-y-3">
            <h2 class="text-xs font-semibold uppercase tracking-wider text-slate-400">Formulário de revisão</h2>

            <div class="card space-y-5">
                <div>
                    <label for="enrichedTitle" class="label-dark mb-1">Título enriquecido <span class="text-danger-400">*</span></label>
                    <input id="enrichedTitle" type="text" wire:model="enrichedTitle" maxlength="500"
                           class="input-dark @error('enrichedTitle') input-dark-error @enderror" />
                    @error('enrichedTitle') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="enrichedDescription" class="label-dark mb-1">Descrição enriquecida <span class="text-danger-400">*</span></label>
                    <textarea id="enrichedDescription" wire:model="enrichedDescription" rows="8"
                              class="input-dark @error('enrichedDescription') input-dark-error @enderror"></textarea>
                    @error('enrichedDescription') <p class="error-text">{{ $message }}</p> @enderror
                </div>

                <div>
                    <p class="label-dark mb-2">Categorias</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($this->availableLabels as $label)
                            <label class="flex cursor-pointer items-center gap-2 rounded-lg px-3 py-2 ring-1 ring-inset transition-all
                                          {{ in_array($label->id, $selectedLabels) ? 'bg-brand-500/15 ring-brand-400/50' : 'bg-white/[0.03] ring-white/10 hover:ring-white/25' }}">
                                <input type="checkbox" wire:model="selectedLabels" value="{{ $label->id }}" class="sr-only">
                                <span class="h-3 w-3 shrink-0 rounded-full" style="background-color: {{ $label->color }};"></span>
                                <span class="text-sm font-medium text-slate-200">{{ $label->name }}</span>
                            </label>
                        @endforeach
                        @if($this->availableLabels->isEmpty())
                            <p class="text-sm text-slate-500">Nenhuma categoria cadastrada.</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 border-t border-white/10 pt-4">
                    <button type="button" wire:click="approve" wire:loading.attr="disabled" wire:target="approve"
                            class="btn-success">
                        <svg wire:loading.remove wire:target="approve" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                        </svg>
                        <svg wire:loading wire:target="approve" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                        </svg>
                        <span wire:loading.remove wire:target="approve">Aprovar</span>
                        <span wire:loading wire:target="approve">Aprovando…</span>
                    </button>

                    <button type="button" wire:click="reject" class="btn-danger">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Rejeitar
                    </button>

                    @if($report->status->canBePublished())
                        <button type="button" wire:click="publish" wire:loading.attr="disabled" wire:target="publish"
                                class="btn-primary">
                            <svg wire:loading.remove wire:target="publish" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
                            </svg>
                            <svg wire:loading wire:target="publish" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                            <span wire:loading.remove wire:target="publish">Publicar para votação</span>
                            <span wire:loading wire:target="publish">Publicando…</span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Rejeição --}}
    <div x-show="showRejectModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center p-4"
         aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-black/70 backdrop-blur-md" x-on:click="showRejectModal = false"></div>

        <div class="relative z-10 w-full max-w-md overflow-hidden rounded-2xl border border-white/10 bg-surface-700/95 p-6 shadow-glass backdrop-blur-xl"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-h3 text-white">Rejeitar ticket</h3>
                <button type="button" x-on:click="showRejectModal = false"
                        class="text-slate-400 transition hover:text-white">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <p class="mb-4 text-sm text-slate-300">
                Informe o motivo da rejeição. Esta informação será registrada internamente.
            </p>

            <div class="mb-4">
                <label for="rejectReason" class="label-dark mb-1">Motivo <span class="text-danger-400">*</span></label>
                <textarea id="rejectReason" wire:model="rejectReason" rows="4"
                          placeholder="Descreva o motivo da rejeição…"
                          class="input-dark @error('rejectReason') input-dark-error @enderror"></textarea>
                @error('rejectReason') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="flex justify-end gap-3">
                <button type="button" x-on:click="showRejectModal = false" class="btn-secondary">
                    Cancelar
                </button>
                <button type="button" wire:click="confirmReject" wire:loading.attr="disabled" wire:target="confirmReject"
                        class="btn-danger">
                    <svg wire:loading wire:target="confirmReject" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <span wire:loading.remove wire:target="confirmReject">Confirmar rejeição</span>
                    <span wire:loading wire:target="confirmReject">Rejeitando…</span>
                </button>
            </div>
        </div>
    </div>
</div>
