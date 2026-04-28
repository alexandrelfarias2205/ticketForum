<div x-data="{ jiraTokenVisible: false, githubTokenVisible: false, gitlabTokenVisible: false }">
    <div class="mb-6 flex items-center gap-4">
        <a href="{{ route('root.products.index') }}" class="text-slate-400 transition hover:text-white">
            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
            </svg>
        </a>
        <div>
            <h1 class="page-title">Integrações</h1>
            <p class="page-subtitle">{{ $product->name }}</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Jira --}}
        <form wire:submit="saveJira" class="card space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">Jira</h2>
                <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                    <input type="checkbox" wire:model="jira.is_active" class="rounded border-white/20 bg-white/5">
                    Ativo
                </label>
            </div>

            <div>
                <label class="label-dark">URL base</label>
                <input wire:model="jira.base_url" type="url" placeholder="https://acme.atlassian.net"
                       class="input-dark mt-1.5 @error('jira.base_url') input-dark-error @enderror">
                @error('jira.base_url') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Chave do projeto</label>
                <input wire:model="jira.project_key" type="text" placeholder="PROJ"
                       class="input-dark mt-1.5 @error('jira.project_key') input-dark-error @enderror">
                @error('jira.project_key') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">E-mail</label>
                <input wire:model="jira.email" type="email" placeholder="agent@empresa.com"
                       class="input-dark mt-1.5 @error('jira.email') input-dark-error @enderror">
                @error('jira.email') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Token de API</label>
                <div class="relative mt-1.5">
                    <input wire:model="jira.api_token" :type="jiraTokenVisible ? 'text' : 'password'"
                           class="input-dark w-full pr-12 @error('jira.api_token') input-dark-error @enderror">
                    <button type="button" @click="jiraTokenVisible = !jiraTokenVisible"
                            class="absolute inset-y-0 right-2 inline-flex items-center text-xs text-slate-400 hover:text-white">
                        <span x-text="jiraTokenVisible ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>
                @error('jira.api_token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-white/10 pt-4">
                <button type="button" wire:click="testConnection('jira')" class="btn-secondary text-sm">Testar conexão</button>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary text-sm">
                    <span wire:loading.remove wire:target="saveJira">Salvar</span>
                    <span wire:loading wire:target="saveJira">Salvando…</span>
                </button>
            </div>
        </form>

        {{-- GitHub --}}
        <form wire:submit="saveGitHub" class="card space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">GitHub</h2>
                <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                    <input type="checkbox" wire:model="github.is_active" class="rounded border-white/20 bg-white/5">
                    Ativo
                </label>
            </div>

            <div>
                <label class="label-dark">Owner</label>
                <input wire:model="github.owner" type="text" placeholder="acme-corp"
                       class="input-dark mt-1.5 @error('github.owner') input-dark-error @enderror">
                @error('github.owner') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Repositório</label>
                <input wire:model="github.repo" type="text" placeholder="meu-repo"
                       class="input-dark mt-1.5 @error('github.repo') input-dark-error @enderror">
                @error('github.repo') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Token de API</label>
                <div class="relative mt-1.5">
                    <input wire:model="github.token" :type="githubTokenVisible ? 'text' : 'password'"
                           class="input-dark w-full pr-12 @error('github.token') input-dark-error @enderror">
                    <button type="button" @click="githubTokenVisible = !githubTokenVisible"
                            class="absolute inset-y-0 right-2 inline-flex items-center text-xs text-slate-400 hover:text-white">
                        <span x-text="githubTokenVisible ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>
                @error('github.token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-white/10 pt-4">
                <button type="button" wire:click="testConnection('github')" class="btn-secondary text-sm">Testar conexão</button>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary text-sm">
                    <span wire:loading.remove wire:target="saveGitHub">Salvar</span>
                    <span wire:loading wire:target="saveGitHub">Salvando…</span>
                </button>
            </div>
        </form>

        {{-- GitLab --}}
        <form wire:submit="saveGitLab" class="card space-y-4">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-bold text-white">GitLab</h2>
                <label class="inline-flex items-center gap-2 text-xs text-slate-300">
                    <input type="checkbox" wire:model="gitlab.is_active" class="rounded border-white/20 bg-white/5">
                    Ativo
                </label>
            </div>

            <div>
                <label class="label-dark">URL base</label>
                <input wire:model="gitlab.base_url" type="url" placeholder="https://gitlab.com"
                       class="input-dark mt-1.5 @error('gitlab.base_url') input-dark-error @enderror">
                @error('gitlab.base_url') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">ID do projeto</label>
                <input wire:model="gitlab.project_id" type="text" placeholder="12345"
                       class="input-dark mt-1.5 @error('gitlab.project_id') input-dark-error @enderror">
                @error('gitlab.project_id') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Token de API</label>
                <div class="relative mt-1.5">
                    <input wire:model="gitlab.token" :type="gitlabTokenVisible ? 'text' : 'password'"
                           class="input-dark w-full pr-12 @error('gitlab.token') input-dark-error @enderror">
                    <button type="button" @click="gitlabTokenVisible = !gitlabTokenVisible"
                            class="absolute inset-y-0 right-2 inline-flex items-center text-xs text-slate-400 hover:text-white">
                        <span x-text="gitlabTokenVisible ? 'Ocultar' : 'Mostrar'"></span>
                    </button>
                </div>
                @error('gitlab.token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div class="flex flex-wrap items-center justify-end gap-2 border-t border-white/10 pt-4">
                <button type="button" wire:click="testConnection('gitlab')" class="btn-secondary text-sm">Testar conexão</button>
                <button type="submit" wire:loading.attr="disabled" class="btn-primary text-sm">
                    <span wire:loading.remove wire:target="saveGitLab">Salvar</span>
                    <span wire:loading wire:target="saveGitLab">Salvando…</span>
                </button>
            </div>
        </form>
    </div>
</div>
