<div class="mx-auto max-w-4xl space-y-6">

    {{-- Cabeçalho --}}
    <div class="card">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0 flex-1">
                <div class="mb-2 flex flex-wrap items-center gap-2">
                    <x-badge :status="$report->type" />
                    <x-badge :status="$report->status" />
                </div>
                <h1 class="text-h2 text-white">
                    {{ $report->enriched_title ?? $report->title }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-slate-400">
                    <span>Por <span class="font-medium text-slate-200">{{ $report->author->name }}</span></span>
                    <span>{{ $report->tenant->name }}</span>
                    <span>{{ $report->created_at->format('d/m/Y \à\s H:i') }}</span>
                    @if($report->status->canReceiveVotes())
                        <span class="flex items-center gap-1 font-semibold text-brand-300">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904" />
                            </svg>
                            {{ $report->vote_count }} votos
                        </span>
                    @endif
                </div>
            </div>

            @if($report->status === \App\Enums\ReportStatus::PendingReview && auth()->id() === $report->author_id)
                <a href="{{ route('app.reports.edit', $report) }}" wire:navigate class="btn-secondary shrink-0">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Editar
                </a>
            @endif
        </div>
    </div>

    {{-- Banners de status --}}
    @if($report->status === \App\Enums\ReportStatus::PendingReview)
        <div class="flex gap-3 rounded-xl border border-warning-400/30 bg-warning-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-warning-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm text-warning-200">
                <span class="font-semibold text-warning-300">Aguardando revisão da equipe.</span>
                Seu ticket foi recebido e será analisado em breve.
            </p>
        </div>
    @elseif($report->status === \App\Enums\ReportStatus::Rejected)
        <div class="flex gap-3 rounded-xl border border-danger-400/30 bg-danger-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-danger-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" /></svg>
            <div>
                <p class="text-sm font-semibold text-danger-300">Ticket não aprovado</p>
                <p class="mt-0.5 text-sm text-danger-200">Este ticket foi revisado e não atendeu aos critérios para publicação.</p>
            </div>
        </div>
    @elseif(in_array($report->status, [\App\Enums\ReportStatus::Approved, \App\Enums\ReportStatus::PublishedForVoting, \App\Enums\ReportStatus::InProgress, \App\Enums\ReportStatus::Done]))
        <div class="flex gap-3 rounded-xl border border-success-400/30 bg-success-500/10 p-4">
            <svg class="mt-0.5 h-5 w-5 shrink-0 text-success-400" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
            <p class="text-sm text-success-200">
                <span class="font-semibold text-success-300">Ticket aprovado pela equipe.</span>
                {{ $report->status->label() }}.
            </p>
        </div>
    @endif

    {{-- Descrição --}}
    <div class="card">
        <h2 class="section-title mb-3">Descrição</h2>
        <p class="whitespace-pre-wrap text-slate-200">{{ $report->enriched_description ?? $report->description }}</p>
    </div>

    {{-- Labels --}}
    @if($report->labels->isNotEmpty() && in_array($report->status, [\App\Enums\ReportStatus::Approved, \App\Enums\ReportStatus::PublishedForVoting, \App\Enums\ReportStatus::InProgress, \App\Enums\ReportStatus::Done]))
        <div class="card">
            <h2 class="section-title mb-3">Categorias</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($report->labels as $label)
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium ring-1 ring-inset"
                        style="background-color: {{ $label->color }}26; color: {{ $label->color }}; border-color: {{ $label->color }}55;"
                    >
                        <span class="mr-1.5 inline-block h-2 w-2 rounded-full" style="background-color: {{ $label->color }};"></span>
                        {{ $label->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Issue externa --}}
    @if($report->external_issue_url)
        <div class="card">
            <h2 class="section-title mb-3">Issue externa</h2>
            <a href="{{ $report->external_issue_url }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 text-sm font-medium text-brand-300 transition hover:text-brand-200">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                {{ $report->external_issue_url }}
            </a>
        </div>
    @endif

    {{-- Anexos --}}
    <div class="card">
        <h2 class="section-title mb-4">Anexos</h2>

        @php
            $isAuthor = auth()->id() === $report->author_id;
            $isPending = $report->status === \App\Enums\ReportStatus::PendingReview;
            $canEdit = $isAuthor && $isPending;
            $images = $report->attachments->filter(fn($a) => $a->isImage());
            $links = $report->attachments->filter(fn($a) => $a->isLink());
        @endphp

        @if($images->isNotEmpty())
            <div class="mb-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Imagens</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($images as $attachment)
                        <div class="group relative aspect-square overflow-hidden rounded-lg border border-white/10 bg-white/5">
                            <img src="{{ $attachment->url }}" alt="{{ $attachment->filename }}"
                                 class="h-full w-full object-cover" loading="lazy">
                            @if($canEdit)
                                <button wire:click="deleteAttachment('{{ $attachment->id }}')"
                                        wire:confirm="Remover esta imagem?"
                                        class="absolute right-1 top-1 hidden h-6 w-6 items-center justify-center rounded-full bg-danger-500 text-white shadow transition hover:bg-danger-400 group-hover:flex"
                                        title="Remover imagem">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        @if($links->isNotEmpty())
            <div class="mb-4">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Links</p>
                <ul class="space-y-2">
                    @foreach($links as $attachment)
                        <li class="flex items-center gap-2 rounded-lg border border-white/10 bg-white/[0.03] px-3 py-2">
                            <svg class="h-4 w-4 shrink-0 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <a href="{{ $attachment->url }}" target="_blank" rel="noopener noreferrer"
                               class="flex-1 truncate text-sm text-brand-300 transition hover:text-brand-200">
                                {{ $attachment->url }}
                            </a>
                            @if($canEdit)
                                <button wire:click="deleteAttachment('{{ $attachment->id }}')"
                                        wire:confirm="Remover este link?"
                                        class="shrink-0 text-slate-500 transition hover:text-danger-400"
                                        title="Remover link">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($images->isEmpty() && $links->isEmpty() && !$canEdit)
            <p class="text-sm text-slate-500">Nenhum anexo adicionado.</p>
        @endif

        @if($canEdit)
            <div class="mt-4 space-y-4 border-t border-white/10 pt-4">
                <div
                    x-data="fileUpload('{{ route('app.reports.attachments.store', $report) }}')"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="handleDrop($event)"
                >
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Adicionar imagem</p>
                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 transition-colors"
                        :class="dragging ? 'border-brand-400 bg-brand-500/10' : 'border-white/15 bg-white/[0.03] hover:border-white/30 hover:bg-white/[0.05]'"
                    >
                        <input type="file" class="sr-only" accept="image/*" multiple x-on:change="handleFiles($event.target.files)">
                        <svg class="mb-2 h-8 w-8 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <p class="text-sm text-slate-300">Clique ou arraste imagens aqui</p>
                        <p class="mt-0.5 text-xs text-slate-500">PNG, JPG, GIF até 10MB</p>
                    </label>

                    <template x-if="uploading">
                        <div class="mt-2">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-white/10">
                                <div class="h-1.5 rounded-full bg-gradient-brand transition-all" :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="mt-1 text-xs text-slate-400" x-text="`Enviando… ${progress}%`"></p>
                        </div>
                    </template>

                    <template x-if="uploadError">
                        <p class="mt-2 text-sm text-danger-400" x-text="uploadError"></p>
                    </template>
                </div>

                <div>
                    <p class="mb-2 text-xs font-semibold uppercase tracking-wider text-slate-400">Adicionar link</p>
                    <div class="flex gap-2">
                        <input type="url" wire:model="newLink" wire:keydown.enter.prevent="addLink"
                               placeholder="https://exemplo.com" class="input-dark flex-1" />
                        <button type="button" wire:click="addLink" wire:loading.attr="disabled" wire:target="addLink"
                                class="btn-secondary shrink-0">
                            Adicionar
                        </button>
                    </div>
                    @error('newLink') <p class="error-text">{{ $message }}</p> @enderror
                </div>
            </div>
        @endif
    </div>

    {{-- Voltar --}}
    <div>
        <a href="{{ route('app.reports.index') }}" wire:navigate
           class="inline-flex items-center gap-2 text-sm text-slate-400 transition hover:text-slate-200">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Voltar para meus tickets
        </a>
    </div>
</div>
