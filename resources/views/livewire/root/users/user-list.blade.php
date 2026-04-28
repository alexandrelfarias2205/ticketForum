<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Usuários</h1>
            <p class="page-subtitle">Gerencie todos os usuários da plataforma.</p>
        </div>
        <a href="{{ route('root.users.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo usuário
        </a>
    </div>

    {{-- Busca --}}
    <div class="card-compact mb-4">
        <div class="relative max-w-sm">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
            </svg>
            <input wire:model.live.debounce.300ms="search" type="search"
                   placeholder="Pesquisar por nome ou e-mail…"
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
                        <th>E-mail</th>
                        <th>Empresa</th>
                        <th>Papel</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->users as $user)
                        <tr>
                            <td class="font-medium text-white">{{ $user->name }}</td>
                            <td class="text-slate-400">{{ $user->email }}</td>
                            <td class="text-slate-300">{{ $user->tenant?->name ?? 'Root' }}</td>
                            <td class="text-slate-300">{{ $user->role->label() }}</td>
                            <td>
                                <x-badge :status="$user->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('root.users.edit', $user) }}"
                                       class="text-sm font-medium text-brand-300 transition hover:text-brand-200">
                                        Editar
                                    </a>
                                    @if($user->is_active)
                                        <button wire:click="deactivate('{{ $user->id }}')"
                                                wire:confirm="Tem certeza que deseja desativar este usuário?"
                                                class="text-sm font-medium text-danger-400 transition hover:text-danger-300">
                                            Desativar
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-12 w-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <p class="font-medium text-slate-300">Nenhum usuário encontrado.</p>
                                <p class="mt-1 text-sm text-slate-500">Tente ajustar a busca ou cadastre um novo usuário.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($this->users->hasPages())
        <div class="mt-4">
            {{ $this->users->links() }}
        </div>
    @endif
</div>
