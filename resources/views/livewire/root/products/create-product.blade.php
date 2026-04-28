<div class="mx-auto max-w-2xl">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('root.products.index') }}" class="text-slate-400 transition hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="page-title">Novo produto</h1>
            <p class="page-subtitle">Cadastre um produto global da plataforma</p>
        </div>
    </div>

    <form wire:submit="save" class="card space-y-6">
        <div>
            <label for="name" class="label-dark">Nome <span class="text-danger-400">*</span></label>
            <input wire:model="name" id="name" type="text"
                   class="input-dark mt-1.5 @error('name') input-dark-error @enderror"
                   placeholder="Ex.: Plataforma de pagamentos" />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="description" class="label-dark">Descrição</label>
            <textarea wire:model="description" id="description" rows="3"
                      class="input-dark mt-1.5 @error('description') input-dark-error @enderror"
                      placeholder="Resumo curto do produto"></textarea>
            @error('description') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="repository_url" class="label-dark">URL do repositório</label>
            <input wire:model="repository_url" id="repository_url" type="url"
                   class="input-dark mt-1.5 @error('repository_url') input-dark-error @enderror"
                   placeholder="https://github.com/owner/repo" />
            @error('repository_url') <p class="error-text">{{ $message }}</p> @enderror
            <p class="help-text mt-1">Opcional — facilita encontrar o código-fonte associado.</p>
        </div>

        <div>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <span class="label-dark">Ativo</span>
                    <p class="help-text">Apenas produtos ativos podem receber novos tickets.</p>
                </div>
                <button type="button" wire:click="$toggle('is_active')"
                        :class="$wire.is_active ? 'bg-gradient-brand' : 'bg-white/10'"
                        class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus-ring"
                        role="switch">
                    <span :class="$wire.is_active ? 'translate-x-5' : 'translate-x-0'"
                          class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow transition duration-200 ease-in-out"></span>
                </button>
            </div>
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('root.products.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove wire:target="save">Criar produto</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </button>
        </div>
    </form>
</div>
