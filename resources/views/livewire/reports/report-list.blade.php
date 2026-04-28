<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Meus tickets</h1>
            <p class="page-subtitle">Acompanhe o status dos seus relatórios enviados.</p>
        </div>
        <a
            href="{{ route('app.reports.create') }}"
            wire:navigate
            class="btn-primary"
        >
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo ticket
        </a>
    </div>

    {{-- Filtros --}}
    <div class="card-compact mb-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input
                    wire:model.live.debounce.300ms="search"
                    type="search"
                    placeholder="Buscar por título ou descrição…"
                    class="input-dark pl-10"
                />
            </div>

            <select wire:model.live="filterType" class="input-dark sm:w-48">
                <option value="">Todos os tipos</option>
                <option value="bug">Bug</option>
                <option value="improvement">Melhoria</option>
                <option value="feature_request">Nova Funcionalidade</option>
            </select>

            <select wire:model.live="filterStatus" class="input-dark sm:w-56">
                <option value="">Todos os status</option>
                <option value="pending_review">Aguardando Revisão</option>
                <option value="approved">Aprovado</option>
                <option value="rejected">Rejeitado</option>
                <option value="published_for_voting">Em Votação</option>
                <option value="in_progress">Em Desenvolvimento</option>
                <option value="done">Concluído</option>
            </select>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Votos</th>
                        <th>Data</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->reports as $report)
                        <tr class="table-row">
                            <td>
                                <p class="max-w-xs truncate font-medium text-white">{{ $report->title }}</p>
                                @if($report->labels->isNotEmpty())
                                    <div class="mt-1 flex flex-wrap gap-1">
                                        @foreach($report->labels->take(3) as $label)
                                            <span
                                                class="inline-flex items-center rounded-full px-2 py-0.5 text-[0.65rem] font-medium ring-1 ring-inset"
                                                style="background-color: {{ $label->color }}26; color: {{ $label->color }}; border-color: {{ $label->color }}55;"
                                            >{{ $label->name }}</span>
                                        @endforeach
                                    </div>
                                @endif
                            </td>
                            <td>
                                <x-badge :status="$report->type" />
                            </td>
                            <td>
                                <x-badge :status="$report->status" />
                            </td>
                            <td>
                                @if($report->status->canReceiveVotes())
                                    <span class="font-semibold text-white">{{ $report->vote_count }}</span>
                                @else
                                    <span class="text-slate-500">—</span>
                                @endif
                            </td>
                            <td class="whitespace-nowrap text-slate-400">
                                {{ $report->created_at->format('d/m/Y') }}
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-secondary-button as="a" href="{{ route('app.reports.show', $report) }}" wire:navigate>
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Ver
                                    </x-secondary-button>
                                    @if($report->status === \App\Enums\ReportStatus::PendingReview)
                                        <x-secondary-button as="a" href="{{ route('app.reports.edit', $report) }}" wire:navigate>
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                            </svg>
                                            Editar
                                        </x-secondary-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-14 w-14 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                <h3 class="font-semibold text-slate-200">Nenhum ticket encontrado</h3>
                                <p class="mt-1 text-sm text-slate-500">Tente ajustar os filtros ou crie um novo ticket.</p>
                                <div class="mt-4">
                                    <x-primary-button as="a" href="{{ route('app.reports.create') }}" wire:navigate>
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Novo ticket
                                    </x-primary-button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Paginação --}}
    @if($this->reports->hasPages())
        <div class="mt-4">
            {{ $this->reports->links() }}
        </div>
    @endif
</div>
