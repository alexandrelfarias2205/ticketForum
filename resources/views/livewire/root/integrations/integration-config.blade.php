<div>
    <div class="mb-6">
        <h1 class="page-title">Configuração de integração</h1>
        <p class="page-subtitle">{{ $tenant->name }}</p>
        <div class="mt-3">
            @if($existingPlatform)
                <span class="badge badge-success">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" /></svg>
                    Integração ativa: {{ ucfirst($existingPlatform) }}
                </span>
            @else
                <span class="badge badge-neutral">Nenhuma integração configurada</span>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mb-6 border-b border-white/10">
        <nav class="-mb-px flex space-x-8">
            <button wire:click="$set('platform', 'jira')"
                    @class([
                        'border-b-2 px-1 py-2 text-sm font-medium transition',
                        'border-brand-400 text-white' => $platform === 'jira',
                        'border-transparent text-slate-400 hover:border-white/20 hover:text-slate-200' => $platform !== 'jira',
                    ])>
                Jira
            </button>
            <button wire:click="$set('platform', 'github')"
                    @class([
                        'border-b-2 px-1 py-2 text-sm font-medium transition',
                        'border-brand-400 text-white' => $platform === 'github',
                        'border-transparent text-slate-400 hover:border-white/20 hover:text-slate-200' => $platform !== 'github',
                    ])>
                GitHub Issues
            </button>
        </nav>
    </div>

    {{-- Jira --}}
    @if($platform === 'jira')
        <form wire:submit="saveJira" class="card max-w-lg space-y-5">
            <div>
                <label class="label-dark">E-mail Atlassian</label>
                <input type="email" wire:model="jiraEmail"
                       placeholder="voce@exemplo.com"
                       class="input-dark mt-1.5 @error('jiraEmail') input-dark-error @enderror" />
                @error('jiraEmail') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Token de API</label>
                <input type="password" wire:model="jiraApiToken"
                       placeholder="••••••••"
                       class="input-dark mt-1.5 @error('jiraApiToken') input-dark-error @enderror" />
                @error('jiraApiToken') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">URL base</label>
                <input type="url" wire:model="jiraBaseUrl"
                       placeholder="https://suaempresa.atlassian.net"
                       class="input-dark mt-1.5 @error('jiraBaseUrl') input-dark-error @enderror" />
                @error('jiraBaseUrl') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Chave do projeto</label>
                <input type="text" wire:model="jiraProjectKey"
                       placeholder="PROJ"
                       class="input-dark mt-1.5 @error('jiraProjectKey') input-dark-error @enderror" />
                @error('jiraProjectKey') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary">Salvar configuração do Jira</button>
        </form>
    @endif

    {{-- GitHub --}}
    @if($platform === 'github')
        <form wire:submit="saveGitHub" class="card max-w-lg space-y-5">
            <div>
                <label class="label-dark">Token de acesso pessoal</label>
                <input type="password" wire:model="githubToken"
                       placeholder="ghp_••••••••"
                       class="input-dark mt-1.5 @error('githubToken') input-dark-error @enderror" />
                @error('githubToken') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Dono do repositório</label>
                <input type="text" wire:model="githubOwner"
                       placeholder="acme-org"
                       class="input-dark mt-1.5 @error('githubOwner') input-dark-error @enderror" />
                @error('githubOwner') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="label-dark">Nome do repositório</label>
                <input type="text" wire:model="githubRepo"
                       placeholder="meu-repo"
                       class="input-dark mt-1.5 @error('githubRepo') input-dark-error @enderror" />
                @error('githubRepo') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="btn-primary">Salvar configuração do GitHub</button>
        </form>
    @endif
</div>
