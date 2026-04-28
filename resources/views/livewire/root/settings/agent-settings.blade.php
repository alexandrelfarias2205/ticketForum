<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Configurações do Agente</h1>
        <p class="mt-1 text-sm text-white/60">Ajuste os parâmetros de comportamento do agente de IA autônomo.</p>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm p-6 shadow-xl">

        {{-- Habilitar agente --}}
        <div class="flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-white">Agente habilitado</label>
                <p class="text-xs text-white/50 mt-0.5">Ativa ou desativa o processamento autônomo de relatórios pelo agente.</p>
            </div>
            <button type="button"
                wire:click="$toggle('agentEnabled')"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $agentEnabled ? 'bg-indigo-500' : 'bg-white/20' }}">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $agentEnabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        @error('agentEnabled') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

        <hr class="border-white/10">

        {{-- Modelo de IA --}}
        <div>
            <label for="aiModel" class="block text-sm font-medium text-white mb-1.5">Modelo de IA</label>
            <select id="aiModel" wire:model="aiModel"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
                <option value="claude-haiku-4-5-20251001">Claude Haiku 4.5</option>
                <option value="claude-sonnet-4-6">Claude Sonnet 4.6</option>
                <option value="claude-opus-4-7">Claude Opus 4.7</option>
            </select>
            @error('aiModel') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Limiar de risco --}}
        <div>
            <label for="riskThreshold" class="block text-sm font-medium text-white mb-1.5">
                Limiar de risco
                <span class="ml-2 text-xs text-white/50">({{ $riskThreshold }}%)</span>
            </label>
            <input id="riskThreshold" type="range" wire:model.live="riskThreshold" min="0" max="100" step="1"
                class="w-full accent-indigo-500">
            <div class="flex justify-between text-xs text-white/40 mt-1">
                <span>0%</span>
                <span>100%</span>
            </div>
            @error('riskThreshold') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Limiar de similaridade --}}
        <div>
            <label for="similarityThreshold" class="block text-sm font-medium text-white mb-1.5">
                Limiar de similaridade
                <span class="ml-2 text-xs text-white/50">({{ number_format($similarityThreshold, 2) }})</span>
            </label>
            <input id="similarityThreshold" type="range" wire:model.live="similarityThreshold" min="0" max="1" step="0.01"
                class="w-full accent-indigo-500">
            <div class="flex justify-between text-xs text-white/40 mt-1">
                <span>0.00</span>
                <span>1.00</span>
            </div>
            @error('similarityThreshold') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Máximo de tentativas --}}
        <div>
            <label for="maxRetries" class="block text-sm font-medium text-white mb-1.5">Máximo de tentativas</label>
            <input id="maxRetries" type="number" wire:model="maxRetries" min="1" max="5"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            @error('maxRetries') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-transparent transition-colors">
                Salvar configurações
            </button>
        </div>
    </form>
</div>
