<div>
    {{-- Page header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Empresas</h1>
            <p class="mt-1 text-sm text-gray-500">Gerencie todas as empresas cadastradas.</p>
        </div>
        <a href="{{ route('root.tenants.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Nova Empresa
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Pesquisar por nome ou slug…"
            class="w-full max-w-sm rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Nome</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Slug</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Plano</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Status</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-600 uppercase tracking-wider text-xs">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($this->tenants as $tenant)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $tenant->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $tenant->slug }}</td>
                        <td class="px-6 py-4 text-gray-700">{{ $tenant->plan->label() }}</td>
                        <td class="px-6 py-4">
                            @if($tenant->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Ativo</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('root.tenants.edit', $tenant) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors">
                                    Editar
                                </a>
                                @if($tenant->is_active)
                                    <button
                                        wire:click="deactivate('{{ $tenant->id }}')"
                                        wire:confirm="Tem certeza que deseja desativar esta empresa?"
                                        class="text-red-600 hover:text-red-900 font-medium transition-colors">
                                        Desativar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                            </svg>
                            <p class="font-medium">Nenhuma empresa encontrada.</p>
                            <p class="mt-1 text-sm">Tente ajustar a busca ou cadastre uma nova empresa.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($this->tenants->hasPages())
        <div class="mt-4">
            {{ $this->tenants->links() }}
        </div>
    @endif
</div>
