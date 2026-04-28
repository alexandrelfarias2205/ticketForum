<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="page-title">Fila de revisão</h1>
        <p class="page-subtitle">Revise e aprove tickets enviados pelas empresas.</p>
    </div>

    {{-- Filtros --}}
    <div class="card-compact mb-4">
        <div class="flex flex-col gap-3 sm:flex-row">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input wire:model.live.debounce.300ms="search" type="search"
                       placeholder="Buscar por título…"
                       class="input-dark pl-10" />
            </div>

            <select wire:model.live="filterStatus" class="input-dark sm:w-56">
                <option value="">Todos os status</option>
                <option value="pending_review">Aguardando Revisão</option>
                <option value="approved">Aprovado</option>
                <option value="rejected">Rejeitado</option>
                <option value="published_for_voting">Em Votação</option>
                <option value="in_progress">Em Desenvolvimento</option>
                <option value="done">Concluído</option>
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
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Empresa</th>
                        <th>Autor</th>
                        <th>Título</th>
                        <th>Tipo</th>
                        <th>Status</th>
                        <th>Data</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->reports as $report)
                        <tr class="table-row">
                            <td class="font-medium text-white">{{ $report->tenant->name }}</td>
                            <td class="text-slate-300">{{ $report->author->name }}</td>
                            <td>
                                <p class="max-w-xs truncate font-medium text-white">{{ $report->title }}</p>
                            </td>
                            <td><x-badge :status="$report->type" /></td>
                            <td><x-badge :status="$report->status" /></td>
                            <td class="whitespace-nowrap text-slate-400">
                                {{ $report->created_at->format('d/m/Y') }}
                            </td>
                            <td class="text-right">
                                <x-secondary-button as="a" href="{{ route('root.reports.show', $report) }}" wire:navigate>
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Revisar
                                </x-secondary-button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-14 w-14 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z" />
                                </svg>
                                <h3 class="font-semibold text-slate-200">Fila de revisão vazia</h3>
                                <p class="mt-1 text-sm text-slate-500">Tente ajustar os filtros de busca.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($this->reports->hasPages())
        <div class="mt-4">
            {{ $this->reports->links() }}
        </div>
    @endif
</div>
