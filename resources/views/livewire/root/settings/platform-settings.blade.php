<div>
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-white">Configurações da Plataforma</h1>
        <p class="mt-1 text-sm text-white/60">Defina as informações gerais e o comportamento global da plataforma.</p>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-2xl border border-white/10 bg-white/5 backdrop-blur-sm p-6 shadow-xl">

        {{-- Nome da plataforma --}}
        <div>
            <label for="platformName" class="block text-sm font-medium text-white mb-1.5">Nome da plataforma</label>
            <input id="platformName" type="text" wire:model="platformName" maxlength="100"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            @error('platformName') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- URL base --}}
        <div>
            <label for="baseUrl" class="block text-sm font-medium text-white mb-1.5">URL base</label>
            <input id="baseUrl" type="url" wire:model="baseUrl" placeholder="https://exemplo.com"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            @error('baseUrl') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- E-mail de suporte --}}
        <div>
            <label for="supportEmail" class="block text-sm font-medium text-white mb-1.5">E-mail de suporte</label>
            <input id="supportEmail" type="email" wire:model="supportEmail" placeholder="suporte@exemplo.com"
                class="w-full rounded-lg border border-white/10 bg-white/5 px-3 py-2.5 text-sm text-white placeholder-white/30 focus:border-indigo-500 focus:outline-none focus:ring-1 focus:ring-indigo-500">
            @error('supportEmail') <p class="mt-1 text-xs text-red-400">{{ $message }}</p> @enderror
        </div>

        {{-- Modo de manutenção --}}
        <div class="flex items-center justify-between">
            <div>
                <label class="text-sm font-medium text-white">Modo de manutenção</label>
                <p class="text-xs text-white/50 mt-0.5">Quando ativado, exibe uma página de manutenção para usuários comuns.</p>
            </div>
            <button type="button"
                wire:click="$toggle('maintenanceMode')"
                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none {{ $maintenanceMode ? 'bg-red-500' : 'bg-white/20' }}">
                <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $maintenanceMode ? 'translate-x-6' : 'translate-x-1' }}"></span>
            </button>
        </div>

        @error('maintenanceMode') <p class="text-xs text-red-400">{{ $message }}</p> @enderror

        <div class="flex justify-end pt-2">
            <button type="submit"
                class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-transparent transition-colors">
                Salvar configurações
            </button>
        </div>
    </form>
</div>
