<div>
    {{-- Page header --}}
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Usuários</h1>
            <p class="mt-1 text-sm text-gray-500">Gerencie todos os usuários do sistema.</p>
        </div>
        <a href="{{ route('root.users.create') }}"
           class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo Usuário
        </a>
    </div>

    {{-- Search --}}
    <div class="mb-4">
        <input
            wire:model.live.debounce.300ms="search"
            type="search"
            placeholder="Pesquisar por nome ou e-mail…"
            class="w-full max-w-sm rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm shadow-sm placeholder-gray-400 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500"
        />
    </div>

    {{-- Table --}}
    <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Nome</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">E-mail</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Empresa</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Papel</th>
                    <th class="px-6 py-3 text-left font-semibold text-gray-600 uppercase tracking-wider text-xs">Status</th>
                    <th class="px-6 py-3 text-right font-semibold text-gray-600 uppercase tracking-wider text-xs">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($this->users as $user)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
                        <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                        <td class="px-6 py-4 text-gray-700">
                            {{ $user->tenant?->name ?? 'Root' }}
                        </td>
                        <td class="px-6 py-4 text-gray-700">{{ $user->role->label() }}</td>
                        <td class="px-6 py-4">
                            @if($user->is_active)
                                <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-semibold text-green-800">Ativo</span>
                            @else
                                <span class="inline-flex items-center rounded-full bg-red-100 px-2.5 py-0.5 text-xs font-semibold text-red-800">Inativo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-3">
                                <a href="{{ route('root.users.edit', $user) }}"
                                   class="text-indigo-600 hover:text-indigo-900 font-medium transition-colors">
                                    Editar
                                </a>
                                @if($user->is_active)
                                    <button
                                        wire:click="deactivate('{{ $user->id }}')"
                                        wire:confirm="Tem certeza que deseja desativar este usuário?"
                                        class="text-red-600 hover:text-red-900 font-medium transition-colors">
                                        Desativar
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-16 text-center text-gray-400">
                            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            <p class="font-medium">Nenhum usuário encontrado.</p>
                            <p class="mt-1 text-sm">Tente ajustar a busca ou cadastre um novo usuário.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($this->users->hasPages())
        <div class="mt-4">
            {{ $this->users->links() }}
        </div>
    @endif
</div>
