<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Fila de Revisão</h1>
            <p class="mt-1 text-sm text-gray-500">Revise e aprove relatórios enviados pelas empresas.</p>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Buscar por título…"
            class="flex-1 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />

        <select
            wire:model.live="filterStatus"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todos os status</option>
            <option value="pending_review">Aguardando Revisão</option>
            <option value="approved">Aprovado</option>
            <option value="rejected">Rejeitado</option>
            <option value="published_for_voting">Em Votação</option>
            <option value="in_progress">Em Desenvolvimento</option>
            <option value="done">Concluído</option>
        </select>

        <select
            wire:model.live="filterTenant"
            class="rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm text-gray-700 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        >
            <option value="">Todas as empresas</option>
            @foreach($this->tenants as $tenant)
                <option value="{{ $tenant->id }}">{{ $tenant->name }}</option>
            @endforeach
        </select>
    </div>

    {{-- Tabela --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Empresa</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Autor</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Título</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Status</th>
                    <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600">Data</th>
                    <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wider text-gray-600">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($this->reports as $report)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <span class="font-medium text-gray-900">{{ $report->tenant->name }}</span>
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $report->author->name }}</td>
                        <td class="px-6 py-4">
                            <p class="font-medium text-gray-900 max-w-xs truncate">{{ $report->title }}</p>
                        </td>
                        <td class="px-6 py-4">
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
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $report->status->badgeClasses() }}">
                                {{ $report->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-gray-500 whitespace-nowrap">
                            {{ $report->created_at->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 text-right">
                            <a
                                href="{{ route('root.reports.show', $report) }}"
                                wire:navigate
                                class="inline-flex items-center gap-1 text-indigo-600 hover:text-indigo-900 font-medium transition-colors"
                            >
                                Revisar
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                                </svg>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                            </svg>
                            <p class="font-medium">Nenhum relatório encontrado</p>
                            <p class="mt-1 text-sm">Tente ajustar os filtros de busca.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Paginação --}}
    @if($this->reports->hasPages())
        <div class="mt-4">
            {{ $this->reports->links() }}
        </div>
    @endif
</div>
