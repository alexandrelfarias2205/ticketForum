<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Empresas</h1>
            <p class="page-subtitle">Gerencie todas as empresas cadastradas.</p>
        </div>
        <a href="{{ route('root.tenants.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova empresa
        </a>
    </div>

    {{-- Busca --}}
    <div class="card-compact mb-4">
        <div class="relative max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input wire:model.live.debounce.300ms="search" type="search"
                   placeholder="Pesquisar por nome ou slug…"
                   class="input-dark pl-10" />
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Slug</th>
                        <th>Plano</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->tenants as $tenant)
                        <tr>
                            <td class="font-medium text-white">{{ $tenant->name }}</td>
                            <td class="text-slate-400">{{ $tenant->slug }}</td>
                            <td class="text-slate-300">{{ $tenant->plan->label() }}</td>
                            <td>
                                <x-badge :status="$tenant->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('root.tenants.edit', $tenant) }}"
                                       class="text-sm font-medium text-brand-300 transition hover:text-brand-200">
                                        Editar
                                    </a>
                                    @if($tenant->is_active)
                                        <button wire:click="deactivate('{{ $tenant->id }}')"
                                                wire:confirm="Tem certeza que deseja desativar esta empresa?"
                                                class="text-sm font-medium text-danger-400 transition hover:text-danger-300">
                                            Desativar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                <p class="font-medium text-slate-300">Nenhuma empresa encontrada.</p>
                                <p class="mt-1 text-sm text-slate-500">Tente ajustar a busca ou cadastre uma nova empresa.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($this->tenants->hasPages())
        <div class="mt-4">
            {{ $this->tenants->links() }}
        </div>
    @endif
</div>
