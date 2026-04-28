<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Usuários</h1>
            <p class="page-subtitle">Gerencie os usuários da sua empresa.</p>
        </div>
        <a href="{{ route('app.admin.users.create') }}" class="btn-primary">
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
            <input
                wire:model.live.debounce.300ms="search"
                type="search"
                placeholder="Pesquisar por nome ou e-mail…"
                class="input-dark pl-10"
            />
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Papel</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->users as $user)
                        <tr class="table-row">
                            <td class="font-medium text-white">{{ $user->name }}</td>
                            <td class="text-slate-400">{{ $user->email }}</td>
                            <td class="text-slate-300">{{ $user->role->label() }}</td>
                            <td>
                                <x-badge :status="$user->is_active ? 'active' : 'inactive'" />
                            </td>
                            <td class="text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <x-secondary-button as="a" href="{{ route('app.admin.users.edit', $user) }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        Editar
                                    </x-secondary-button>
                                    @if($user->is_active && $user->id !== auth()->id())
                                        <x-danger-button
                                            wire:click="deactivate('{{ $user->id }}')"
                                            wire:confirm="Tem certeza que deseja desativar este usuário?"
                                            wire:loading.attr="disabled"
                                            wire:target="deactivate('{{ $user->id }}')"
                                        >
                                            <svg wire:loading.remove wire:target="deactivate('{{ $user->id }}')" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            <svg wire:loading wire:target="deactivate('{{ $user->id }}')" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                                                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                            </svg>
                                            Desativar
                                        </x-danger-button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-14 w-14 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                <h3 class="font-semibold text-slate-200">Nenhum usuário encontrado</h3>
                                <p class="mt-1 text-sm text-slate-500">Tente ajustar a busca ou cadastre um novo usuário.</p>
                                <div class="mt-4">
                                    <x-primary-button as="a" href="{{ route('app.admin.users.create') }}">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Novo usuário
                                    </x-primary-button>
                                </div>
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
