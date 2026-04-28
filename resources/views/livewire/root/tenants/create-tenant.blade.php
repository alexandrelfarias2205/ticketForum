<div class="mx-auto max-w-2xl">
    <div class="mb-6">
        <h1 class="page-title">Nova empresa</h1>
        <p class="page-subtitle">Preencha os dados para cadastrar uma nova empresa.</p>
    </div>

    <form wire:submit="save" class="card space-y-6">

        <div>
            <label for="name" class="label-dark">Nome <span class="text-danger-400">*</span></label>
            <input wire:model="name" id="name" type="text" autocomplete="organization"
                   placeholder="Ex.: Acme Corp"
                   class="input-dark mt-1.5 @error('name') input-dark-error @enderror" />
            @error('name') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="plan" class="label-dark">Plano <span class="text-danger-400">*</span></label>
            <select wire:model="plan" id="plan"
                    class="input-dark mt-1.5 @error('plan') input-dark-error @enderror">
                <option value="free">Gratuito</option>
                <option value="pro">Pro</option>
                <option value="enterprise">Enterprise</option>
            </select>
            @error('plan') <p class="error-text">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center justify-end gap-3 border-t border-white/10 pt-6">
            <a href="{{ route('root.tenants.index') }}" class="btn-secondary">Cancelar</a>
            <button type="submit" wire:loading.attr="disabled" class="btn-primary">
                <svg wire:loading wire:target="save" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                    <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                </svg>
                <span wire:loading.remove wire:target="save">Salvar</span>
                <span wire:loading wire:target="save">Salvando…</span>
            </button>
        </div>
    </form>
</div>
