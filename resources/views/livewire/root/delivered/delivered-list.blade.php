<div>
    <div class="mb-6">
        <h1 class="page-title">Entregas</h1>
        <p class="page-subtitle">Tickets concluídos e implementados.</p>
    </div>

    {{-- Filtros --}}
    <div class="card-compact mb-4">
        <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap">
            <input type="text" wire:model.live="search" placeholder="Buscar por título…"
                   class="input-dark sm:w-64" />

            <select wire:model.live="filterTenant" class="input-dark sm:w-52">
                <option value="">Todas as empresas</option>
                @foreach($this->tenants as $tenant)
                    <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
                @endforeach
            </select>

            <select wire:model.live="filterPlatform" class="input-dark sm:w-52">
                <option value="">Todas as plataformas</option>
                <option value="jira">Jira</option>
                <option value="github">GitHub Issues</option>
            </select>
        </div>
    </div>

    <div class="card overflow-hidden p-0">
        @if($this->reports->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="table-dark min-w-full">
                    <thead>
                        <tr>
                            <th>Título</th>
                            <th>Empresa</th>
                            <th>Tipo</th>
                            <th class="text-center">Votos</th>
                            <th>Issue externa</th>
                            <th>Conclusão</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($this->reports as $report)
                            <tr>
                                <td class="max-w-xs">
                                    <span class="block truncate font-medium text-white">{{ $report->title }}</span>
                                </td>
                                <td class="text-slate-400">{{ $report->tenant?->name ?? '—' }}</td>
                                <td><x-badge :status="$report->type" /></td>
                                <td class="text-center">
                                    <span class="font-bold text-brand-300">{{ $report->vote_count }}</span>
                                </td>
                                <td>
                                    @if($report->external_issue_url && $report->external_issue_id)
                                        <div class="flex items-center gap-2">
                                            @if($report->external_platform?->value === 'jira')
                                                <span class="badge badge-info">Jira</span>
                                            @elseif($report->external_platform?->value === 'github')
                                                <span class="badge badge-neutral">GitHub</span>
                                            @endif
                                            <a href="{{ $report->external_issue_url }}" target="_blank" rel="noopener noreferrer"
                                               class="inline-flex items-center gap-1 text-xs font-medium text-brand-300 transition hover:text-brand-200">
                                                {{ $report->external_issue_id }}
                                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                                </svg>
                                            </a>
                                        </div>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap text-xs text-slate-400">
                                    {{ $report->updated_at?->format('d/m/Y') ?? '—' }}
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
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-.723 3.066 3.745 3.745 0 01-3.066.723 3.745 3.745 0 01-3.068 1.593 3.745 3.745 0 01-3.067-1.593 3.745 3.745 0 01-3.066-.723 3.745 3.745 0 01-.723-3.066A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 01.723-3.066 3.745 3.745 0 013.066-.723A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.066.723 3.745 3.745 0 01.723 3.066A3.745 3.745 0 0121 12z" />
                </svg>
                <p class="font-medium text-slate-300">Nenhuma entrega registrada ainda.</p>
            </div>
        @endif
    </div>
</div>
