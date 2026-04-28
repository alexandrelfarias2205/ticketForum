<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="page-title">Ranking de votação</h1>
        <p class="page-subtitle">Sugestões ordenadas pelo número de votos recebidos.</p>
    </div>

    {{-- Filtros --}}
    <div class="card-compact mb-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <input type="text" wire:model.live="search" placeholder="Buscar por título…"
                   class="input-dark sm:w-64" />

            <select wire:model.live="filterType" class="input-dark sm:w-44">
                <option value="">Todos os tipos</option>
                <option value="bug">Bug</option>
                <option value="improvement">Melhoria</option>
                <option value="feature_request">Nova Funcionalidade</option>
            </select>

            <select wire:model.live="filterTenant" class="input-dark sm:w-52">
                <option value="">Todas as empresas</option>
                @foreach($this->tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        @if($this->reports->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="table-dark min-w-full">
                    <thead>
                        <tr>
                            <th class="w-14">#</th>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th>Status</th>
                            <th class="text-center">Votos</th>
                            <th class="text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->reports as $index => $report)
                            @php
                                $position = ($this->reports->currentPage() - 1) * $this->reports->perPage() + $index + 1;
                                $accent = match(true) {
                                    $position === 1 => 'text-warning-300 ring-warning-400/40',
                                    $position === 2 => 'text-slate-300 ring-slate-400/40',
                                    $position === 3 => 'text-warning-500 ring-warning-500/40',
                                    default => 'text-slate-400',
                                };
                            @endphp
                            <tr>
                                <td>
                                    @if($position <= 3)
                                        <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-white/5 ring-1 ring-inset font-bold {{ $accent }}">{{ $position }}</span>
                                    @else
                                        <span class="font-bold text-slate-500">{{ $position }}</span>
                                    @endif
                                </td>
                                <td class="max-w-xs">
                                    <span class="block truncate font-medium text-white">{{ $report->title }}</span>
                                    @if($report->labels->isNotEmpty())
                                        <div class="mt-1 flex flex-wrap gap-1">
                                            @foreach($report->labels as $label)
                                                <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[0.65rem] font-medium ring-1 ring-inset"
                                                      style="background-color: {{ $label->color }}26; color: {{ $label->color }}; border-color: {{ $label->color }}55;">
                                                    {{ $label->name }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-slate-400">{{ $report->tenant?->name ?? '—' }}</td>
                                <td><x-badge :status="$report->type" /></td>
                                <td><x-badge :status="$report->status" /></td>
                                <td class="text-center">
                                    <span class="text-lg font-bold text-brand-300">{{ $report->vote_count }}</span>
                                </td>
                                <td class="text-right">
                                    @if($report->status === \App\Enums\ReportStatus::PublishedForVoting)
                                        <a href="{{ route('root.reports.show', $report) }}"
                                           class="text-xs font-medium text-brand-300 transition hover:text-brand-200">
                                            Ver revisão
                                        </a>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($this->reports->hasPages())
                <div class="border-t border-white/10 px-4 py-3">
                    {{ $this->reports->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="mb-3 h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                </svg>
                <p class="font-medium text-slate-300">Nenhuma sugestão em votação no momento.</p>
            </div>
        @endif
    </div>
</div>
