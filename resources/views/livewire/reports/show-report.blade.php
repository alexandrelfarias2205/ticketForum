<div class="max-w-4xl mx-auto space-y-6">

    {{-- Cabeçalho do relatório --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2 mb-2">
                    {{-- Tipo --}}
                    @php
                        $typeBadge = match($report->type->value) {
                            'bug'             => 'bg-red-100 text-red-700',
                            'improvement'     => 'bg-blue-100 text-blue-700',
                            'feature_request' => 'bg-indigo-100 text-indigo-700',
                            default           => 'bg-gray-100 text-gray-700',
                        };
                    @endphp
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $typeBadge }}">
                        {{ $report->type->label() }}
                    </span>
                    {{-- Status --}}
                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $report->status->badgeClasses() }}">
                        {{ $report->status->label() }}
                    </span>
                </div>
                <h1 class="text-xl font-bold text-gray-900">
                    {{ $report->enriched_title ?? $report->title }}
                </h1>
                <div class="mt-2 flex flex-wrap items-center gap-4 text-sm text-gray-500">
                    <span>Por <span class="font-medium text-gray-700">{{ $report->author->name }}</span></span>
                    <span>{{ $report->tenant->name }}</span>
                    <span>{{ $report->created_at->format('d/m/Y \à\s H:i') }}</span>
                    @if($report->status->canReceiveVotes())
                        <span class="flex items-center gap-1 font-semibold text-indigo-600">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.633 10.5c.806 0 1.533-.446 2.031-1.08a9.041 9.041 0 012.861-2.4c.723-.384 1.35-.956 1.653-1.715a4.498 4.498 0 00.322-1.672V3a.75.75 0 01.75-.75A2.25 2.25 0 0116.5 4.5c0 1.152-.26 2.243-.723 3.218-.266.558.107 1.282.725 1.282h3.126c1.026 0 1.945.694 2.054 1.715.045.422.068.85.068 1.285a11.95 11.95 0 01-2.649 7.521c-.388.482-.987.729-1.605.729H13.48c-.483 0-.964-.078-1.423-.23l-3.114-1.04a4.501 4.501 0 00-1.423-.23H5.904M14.25 9h2.25M5.904 18.75c.083.205.173.405.27.602.197.4-.078.898-.523.898h-.908c-.889 0-1.713-.518-1.972-1.368a12 12 0 01-.521-3.507c0-1.553.295-3.036.831-4.398C3.387 10.203 4.167 9.75 5 9.75h1.053c.472 0 .745.556.5.96a8.958 8.958 0 00-1.302 4.665c0 1.194.232 2.333.654 3.375z" />
                            </svg>
                            {{ $report->vote_count }} votos
                        </span>
                    @endif
                </div>
            </div>

            @if($report->status->value === 'pending_review' && auth()->id() === $report->author_id)
                <a
                    href="{{ route('app.reports.edit', $report) }}"
                    wire:navigate
                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors"
                >
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                    Editar
                </a>
            @endif
        </div>
    </div>

    {{-- Banners de status --}}
    @if($report->status->value === 'pending_review')
        <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-yellow-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-yellow-800">
                    <span class="font-semibold">Aguardando revisão da equipe.</span>
                    Seu relatório foi recebido e será analisado em breve.
                </p>
            </div>
        </div>
    @elseif($report->status->value === 'rejected')
        <div class="rounded-xl border border-red-200 bg-red-50 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-red-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <div>
                    <p class="font-semibold text-sm text-red-800">Relatório não aprovado</p>
                    <p class="mt-0.5 text-sm text-red-700">Este relatório foi revisado e não atendeu aos critérios para publicação.</p>
                </div>
            </div>
        </div>
    @elseif(in_array($report->status->value, ['approved', 'published_for_voting', 'in_progress', 'done']))
        <div class="rounded-xl border border-green-200 bg-green-50 p-4">
            <div class="flex gap-3">
                <svg class="h-5 w-5 flex-shrink-0 text-green-500 mt-0.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p class="text-sm text-green-800">
                    <span class="font-semibold">Relatório aprovado pela equipe.</span>
                    {{ $report->status->label() }}.
                </p>
            </div>
        </div>
    @endif

    {{-- Descrição --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-3">Descrição</h2>
        @if($report->enriched_description)
            <div class="prose prose-sm max-w-none text-gray-700">
                {!! nl2br(e($report->enriched_description)) !!}
            </div>
        @else
            <div class="text-sm text-gray-700 whitespace-pre-wrap">{{ $report->description }}</div>
        @endif
    </div>

    {{-- Labels (se aprovado) --}}
    @if($report->labels->isNotEmpty() && in_array($report->status->value, ['approved', 'published_for_voting', 'in_progress', 'done']))
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Categorias</h2>
            <div class="flex flex-wrap gap-2">
                @foreach($report->labels as $label)
                    <span
                        class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium"
                        style="background-color: {{ $label->color }}22; color: {{ $label->color }};"
                    >
                        <span class="mr-1.5 h-2 w-2 rounded-full inline-block" style="background-color: {{ $label->color }};"></span>
                        {{ $label->name }}
                    </span>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Link externo --}}
    @if($report->external_issue_url)
        <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-gray-900 mb-3">Issue Externa</h2>
            <a
                href="{{ $report->external_issue_url }}"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 text-sm text-indigo-600 hover:text-indigo-800 font-medium transition-colors"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                {{ $report->external_issue_url }}
            </a>
        </div>
    @endif

    {{-- Anexos --}}
    <div class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm">
        <h2 class="text-base font-semibold text-gray-900 mb-4">Anexos</h2>

        @php
            $isAuthor = auth()->id() === $report->author_id;
            $isPending = $report->status->value === 'pending_review';
            $canEdit = $isAuthor && $isPending;
            $images = $report->attachments->filter(fn($a) => $a->isImage());
            $links = $report->attachments->filter(fn($a) => $a->isLink());
        @endphp

        {{-- Imagens --}}
        @if($images->isNotEmpty())
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Imagens</p>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach($images as $attachment)
                        <div class="group relative aspect-square overflow-hidden rounded-lg border border-gray-200 bg-gray-50">
                            <img
                                src="{{ $attachment->url }}"
                                alt="{{ $attachment->filename }}"
                                class="h-full w-full object-cover"
                                loading="lazy"
                            >
                            @if($canEdit)
                                <button
                                    wire:click="deleteAttachment('{{ $attachment->id }}')"
                                    wire:confirm="Remover esta imagem?"
                                    class="absolute top-1 right-1 hidden group-hover:flex items-center justify-center h-6 w-6 rounded-full bg-red-500 text-white shadow hover:bg-red-600 transition-colors"
                                    title="Remover imagem"
                                >
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Links --}}
        @if($links->isNotEmpty())
            <div class="mb-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Links</p>
                <ul class="space-y-2">
                    @foreach($links as $attachment)
                        <li class="flex items-center gap-2 rounded-lg bg-gray-50 border border-gray-200 px-3 py-2">
                            <svg class="h-4 w-4 flex-shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                            <a
                                href="{{ $attachment->url }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="flex-1 truncate text-sm text-indigo-600 hover:text-indigo-800 transition-colors"
                            >
                                {{ $attachment->url }}
                            </a>
                            @if($canEdit)
                                <button
                                    wire:click="deleteAttachment('{{ $attachment->id }}')"
                                    wire:confirm="Remover este link?"
                                    class="flex-shrink-0 text-gray-400 hover:text-red-500 transition-colors"
                                    title="Remover link"
                                >
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($images->isEmpty() && $links->isEmpty() && !$canEdit)
            <p class="text-sm text-gray-400">Nenhum anexo adicionado.</p>
        @endif

        {{-- Upload de imagem (apenas autor + pendente) --}}
        @if($canEdit)
            <div class="mt-4 border-t border-gray-100 pt-4 space-y-4">

                {{-- Zona de upload de imagem --}}
                <div
                    x-data="fileUpload('{{ route('app.reports.attachments.store', $report) }}')"
                    x-on:dragover.prevent="dragging = true"
                    x-on:dragleave.prevent="dragging = false"
                    x-on:drop.prevent="handleDrop($event)"
                >
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Adicionar imagem</p>
                    <label
                        class="flex cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed p-6 transition-colors"
                        :class="dragging ? 'border-indigo-400 bg-indigo-50' : 'border-gray-300 bg-gray-50 hover:border-gray-400'"
                    >
                        <input type="file" class="sr-only" accept="image/*" multiple x-on:change="handleFiles($event.target.files)">
                        <svg class="h-8 w-8 text-gray-400 mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        <p class="text-sm text-gray-500">Clique ou arraste imagens aqui</p>
                        <p class="mt-0.5 text-xs text-gray-400">PNG, JPG, GIF até 10MB</p>
                    </label>

                    {{-- Progress --}}
                    <template x-if="uploading">
                        <div class="mt-2">
                            <div class="h-1.5 w-full overflow-hidden rounded-full bg-gray-200">
                                <div class="h-1.5 rounded-full bg-indigo-500 transition-all" :style="`width: ${progress}%`"></div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500" x-text="`Enviando… ${progress}%`"></p>
                        </div>
                    </template>

                    <template x-if="uploadError">
                        <p class="mt-2 text-sm text-red-600" x-text="uploadError"></p>
                    </template>
                </div>

                {{-- Adicionar link --}}
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 mb-2">Adicionar link</p>
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
                            wire:loading.attr="disabled"
                            wire:target="addLink"
                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors disabled:opacity-60"
                        >
                            Adicionar
                        </button>
                    </div>
                    @error('newLink')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        @endif
    </div>

    {{-- Botão voltar --}}
    <div class="flex">
        <a
            href="{{ route('app.reports.index') }}"
            wire:navigate
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
            Voltar para Relatórios
        </a>
    </div>

</div>
