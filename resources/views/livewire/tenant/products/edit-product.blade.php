<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="page-title">Editar Produto — {{ $product->name }}</h1>
        <p class="page-subtitle">Atualize as informações do produto.</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        {{-- Nome --}}
        <div>
            <label for="name" class="label-dark">Nome <span class="text-danger-400">*</span></label>
            <input
                wire:model="name"
                id="name"
                type="text"
                placeholder="Nome do produto"
                class="input-dark mt-1.5 @error('name') input-dark-error @enderror"
            />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        {{-- Descrição --}}
        <div>
            <label for="description" class="label-dark">Descrição</label>
            <textarea
                wire:model="description"
                id="description"
                rows="4"
                placeholder="Descreva brevemente o produto (opcional)"
                class="input-dark mt-1.5 resize-none @error('description') input-dark-error @enderror"
            ></textarea>
            @error('description') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        {{-- URL do Repositório --}}
        <div>
            <label for="repositoryUrl" class="label-dark">URL do Repositório</label>
            <input
                wire:model="repositoryUrl"
                id="repositoryUrl"
                type="url"
                placeholder="https://github.com/..."
                class="input-dark mt-1.5 @error('repositoryUrl') input-dark-error @enderror"
            />
            @error('repositoryUrl') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        {{-- Ações --}}
        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('app.products.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Salvar alterações</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </button>
        </div>
    </form>
</div>
