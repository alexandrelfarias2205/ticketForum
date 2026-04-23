<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Entregas</h1>
        <p class="mt-1 text-sm text-gray-500">Relatórios concluídos e implementados.</p>
    </div>

    {{-- Filtros --}}
    <div class="mb-4 flex flex-wrap gap-3">
        <input
            type="text"
            wire:model.live="search"
            placeholder="Buscar por título..."
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-64"
        />

        <select
            wire:model.live="filterTenant"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todas as empresas</option>
            @foreach($this->tenants as $tenant)
                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
            @endforeach
        </select>

        <select
            wire:model.live="filterPlatform"
            class="rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todas as plataformas</option>
            <option value="jira">Jira</option>
            <option value="github">GitHub Issues</option>
        </select>
    </div>

    {{-- Tabela --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        @if($this->reports->isNotEmpty())
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Título</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Empresa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Tipo</th>
                        <th class="px-4 py-3 text-center font-semibold text-gray-600">Votos</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Issue Externa</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-600">Conclusão</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($this->reports as $report)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900 max-w-xs">
                                <span class="truncate block">{{ $report->title }}</span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->tenant?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-gray-600">{{ $report->type->label() }}</td>
                            <td class="px-4 py-3 text-center font-bold text-indigo-700">{{ $report->vote_count }}</td>
                            <td class="px-4 py-3">
                                @if($report->external_issue_url && $report->external_issue_id)
                                    <div class="flex items-center gap-2">
                                        @if($report->external_platform?->value === 'jira')
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-blue-100 text-blue-700">Jira</span>
                                        @elseif($report->external_platform?->value === 'github')
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-xs font-medium bg-gray-100 text-gray-700">GitHub</span>
                                        @endif
                                        <a href="{{ $report->external_issue_url }}"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           class="text-indigo-600 hover:text-indigo-800 font-medium text-xs inline-flex items-center gap-1">
                                            {{ $report->external_issue_id }}
                                            <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                            </svg>
                                        </a>
                                    </div>
                                @else
                                    <span class="text-gray-400 text-xs">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-600 text-xs whitespace-nowrap">
                                {{ $report->updated_at?->format('d/m/Y') ?? '—' }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($this->reports->hasPages())
                <div class="border-t border-gray-100 px-4 py-3">
                    {{ $this->reports->links() }}
                </div>
            @endif
        @else
            <div class="flex flex-col items-center justify-center py-16 text-center">
                <svg class="h-12 w-12 text-gray-300 mb-3" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-.723 3.066 3.745 3.745 0 01-3.066.723 3.745 3.745 0 01-3.068 1.593 3.745 3.745 0 01-3.067-1.593 3.745 3.745 0 01-3.066-.723 3.745 3.745 0 01-.723-3.066A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 01.723-3.066 3.745 3.745 0 013.066-.723A3.745 3.745 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.745 3.745 0 013.066.723 3.745 3.745 0 01.723 3.066A3.745 3.745 0 0121 12z" />
                </svg>
                <p class="text-gray-500 font-medium">Nenhuma entrega registrada ainda.</p>
            </div>
        @endif
    </div>
</div>
