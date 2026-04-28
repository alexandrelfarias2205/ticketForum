<div>
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <h1 class="page-title">Produtos</h1>
            <p class="page-subtitle">Catálogo global de produtos da plataforma</p>
        </div>
        <a href="{{ route('root.products.create') }}" class="btn-primary">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            Novo produto
        </a>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-lg border border-success-400/40 bg-success-500/15 px-4 py-3 text-sm text-success-100">
            {{ session('success') }}
        </div>
    @endif

    <div class="card overflow-hidden p-0">
        <table class="min-w-full divide-y divide-white/10">
            <thead class="bg-white/5">
                <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-400">
                    <th class="px-4 py-3">Produto</th>
                    <th class="px-4 py-3">Empresas</th>
                    <th class="px-4 py-3">Tickets</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($this->products as $product)
                    <tr class="text-sm text-slate-200 hover:bg-white/5">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-white">{{ $product->name }}</div>
                            @if($product->description)
                                <div class="mt-0.5 text-xs text-slate-400 line-clamp-1">{{ $product->description }}</div>
                            @endif
                            @if($product->repository_url)
                                <a href="{{ $product->repository_url }}" target="_blank" rel="noopener"
                                   class="mt-0.5 inline-flex items-center gap-1 text-xs text-brand-300 hover:text-brand-200">
                                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" /></svg>
                                    Repositório
                                </a>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge">{{ $product->tenants_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge">{{ $product->reports_count }}</span>
                        </td>
                        <td class="px-4 py-3">
                            @if($product->is_active)
                                <span class="badge badge-success">Ativo</span>
                            @else
                                <span class="badge badge-warning">Arquivado</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="inline-flex items-center gap-2">
                                <a href="{{ route('root.products.edit', $product) }}"
                                   class="btn-secondary px-3 py-1 text-xs">Editar</a>
                                <a href="{{ route('root.products.integrations', $product) }}"
                                   class="btn-secondary px-3 py-1 text-xs">Integrações</a>
                                <button wire:click="toggleActive('{{ $product->id }}')"
                                        wire:confirm="Tem certeza que deseja {{ $product->is_active ? 'arquivar' : 'ativar' }} este produto?"
                                        class="btn-secondary px-3 py-1 text-xs">
                                    {{ $product->is_active ? 'Arquivar' : 'Ativar' }}
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-sm text-slate-400">
                            Nenhum produto cadastrado ainda. Clique em "Novo produto" para criar o primeiro.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
