<div>
    {{-- Cabeçalho --}}
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="page-title">Produtos</h1>
            <p class="page-subtitle">Gerencie os produtos da sua empresa.</p>
        </div>
        <a href="{{ route('app.products.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo produto
        </a>
    </div>

    {{-- Alertas de sessão --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border border-emerald-500/30 bg-emerald-500/10 px-4 py-3 text-sm text-emerald-400">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-400">
            {{ session('error') }}
        </div>
    @endif

    {{-- Tabela --}}
    <div class="card overflow-hidden p-0">
        <div class="overflow-x-auto">
            <table class="table-dark min-w-full">
                <thead class="table-head">
                    <tr>
                        <th>Nome</th>
                        <th>Descrição</th>
                        <th>Repositório</th>
                        <th>Reports</th>
                        <th>Status</th>
                        <th class="text-right">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($this->products as $product)
                        <tr class="table-row">
                            <td class="font-medium text-white">{{ $product->name }}</td>
                            <td class="max-w-xs text-slate-400">
                                @if($product->description)
                                    <span title="{{ $product->description }}">
                                        {{ Str::limit($product->description, 60) }}
                                    </span>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            <td>
                                @if($product->repository_url)
                                    <a
                                        href="{{ $product->repository_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1 text-sm text-indigo-400 hover:text-indigo-300"
                                    >
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                        </svg>
                                        Abrir
                                    </a>
                                @else
                                    <span class="text-slate-600">—</span>
                                @endif
                            </td>
                            <td>
                                <span class="inline-flex items-center gap-1 text-sm text-slate-300">
                                    <svg class="h-3.5 w-3.5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                                    </svg>
                                    {{ $product->reports_count }}
                                </span>
                            </td>
                            <td>
                                <x-badge tone="{{ $product->is_active ? 'success' : 'neutral' }}" label="{{ $product->is_active ? 'Ativo' : 'Inativo' }}" />
                            </td>
                            <td class="text-right">
                                <div
                                    x-data="{ confirmDelete: false }"
                                    class="flex items-center justify-end gap-2"
                                >
                                    {{-- Editar --}}
                                    <x-secondary-button as="a" href="{{ route('app.products.edit', $product) }}">
                                        <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                        </svg>
                                        Editar
                                    </x-secondary-button>

                                    {{-- Ativar / Desativar --}}
                                    <x-secondary-button
                                        wire:click="toggleActive('{{ $product->id }}')"
                                        wire:loading.attr="disabled"
                                        wire:target="toggleActive('{{ $product->id }}')"
                                    >
                                        <svg wire:loading.remove wire:target="toggleActive('{{ $product->id }}')" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                            @if($product->is_active)
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            @else
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5.636 5.636a9 9 0 1012.728 12.728A9 9 0 005.636 5.636z" />
                                            @endif
                                        </svg>
                                        <svg wire:loading wire:target="toggleActive('{{ $product->id }}')" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                            <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                                            <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                        </svg>
                                        {{ $product->is_active ? 'Desativar' : 'Ativar' }}
                                    </x-secondary-button>

                                    {{-- Excluir --}}
                                    <div x-show="!confirmDelete">
                                        <x-danger-button @click="confirmDelete = true">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                            </svg>
                                            Excluir
                                        </x-danger-button>
                                    </div>

                                    <div x-show="confirmDelete" class="flex items-center gap-2" x-cloak>
                                        <span class="text-xs text-slate-400">Confirmar exclusão?</span>
                                        <x-danger-button
                                            wire:click="delete('{{ $product->id }}')"
                                            wire:loading.attr="disabled"
                                            wire:target="delete('{{ $product->id }}')"
                                        >
                                            <svg wire:loading.remove wire:target="delete('{{ $product->id }}')" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                            </svg>
                                            <svg wire:loading wire:target="delete('{{ $product->id }}')" class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                                                <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                                            </svg>
                                            Sim, excluir
                                        </x-danger-button>
                                        <x-secondary-button @click="confirmDelete = false">
                                            Cancelar
                                        </x-secondary-button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <svg class="mx-auto mb-4 h-14 w-14 text-slate-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 7.5l-9-5.25L3 7.5m18 0l-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                <h3 class="font-semibold text-slate-200">Nenhum produto cadastrado</h3>
                                <p class="mt-1 text-sm text-slate-500">Comece criando o primeiro produto da sua empresa.</p>
                                <div class="mt-4">
                                    <a href="{{ route('app.products.create') }}" class="btn-primary">
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                        </svg>
                                        Criar primeiro produto
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
