<div>
    {{-- Cabeçalho --}}
    <div class="mb-6">
        <h1 class="page-title">Integrações</h1>
        <p class="page-subtitle">Configure as integrações externas da sua empresa.</p>
    </div>

    {{-- Grid de cards --}}
    <div class="grid gap-6 md:grid-cols-1 lg:grid-cols-3">

        {{-- ==================== JIRA ==================== --}}
        <div class="card space-y-5">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Logo Jira --}}
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.975 0C9.09 0 6.6 2.17 6.6 5.007c0 1.395.553 2.657 1.44 3.593L12 12.548l3.96-3.948A4.942 4.942 0 0017.35 5.007C17.35 2.17 14.86 0 11.975 0zm0 6.895a1.888 1.888 0 110-3.776 1.888 1.888 0 010 3.776z" fill="#2684FF"/>
                        <path d="M11.975 11.452L8.04 15.4A4.942 4.942 0 006.6 18.993C6.6 21.83 9.09 24 11.975 24c2.886 0 5.375-2.17 5.375-5.007a4.942 4.942 0 00-1.415-3.593l-3.96-3.948zm0 10.65a1.888 1.888 0 110-3.775 1.888 1.888 0 010 3.775z" fill="#2684FF" opacity=".65"/>
                    </svg>
                    <span class="font-semibold text-white">Jira</span>
                </div>
                <x-badge tone="{{ $jira['api_token'] ? 'success' : 'neutral' }}" label="{{ $jira['api_token'] ? 'Configurado' : 'Não configurado' }}" />
            </div>

            {{-- URL base --}}
            <div>
                <label for="jira_base_url" class="label-dark">URL base</label>
                <input
                    wire:model="jira.base_url"
                    id="jira_base_url"
                    type="url"
                    placeholder="https://seu-dominio.atlassian.net"
                    class="input-dark mt-1.5 @error('jira.base_url') input-dark-error @enderror"
                />
                @error('jira.base_url') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- E-mail --}}
            <div>
                <label for="jira_user_email" class="label-dark">E-mail</label>
                <input
                    wire:model="jira.user_email"
                    id="jira_user_email"
                    type="email"
                    placeholder="usuario@empresa.com"
                    class="input-dark mt-1.5 @error('jira.user_email') input-dark-error @enderror"
                />
                @error('jira.user_email') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- API Token com toggle --}}
            <div x-data="{ showJiraToken: false }">
                <label for="jira_api_token" class="label-dark">API Token</label>
                <div class="relative mt-1.5">
                    <input
                        wire:model="jira.api_token"
                        id="jira_api_token"
                        :type="showJiraToken ? 'text' : 'password'"
                        placeholder="••••••••••••••••"
                        class="input-dark pr-10 @error('jira.api_token') input-dark-error @enderror"
                    />
                    <button
                        type="button"
                        @click="showJiraToken = !showJiraToken"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-300 focus:outline-none"
                        :aria-label="showJiraToken ? 'Ocultar token' : 'Mostrar token'"
                    >
                        <svg x-show="!showJiraToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showJiraToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('jira.api_token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Chave do projeto --}}
            <div>
                <label for="jira_project_key" class="label-dark">Chave do projeto</label>
                <input
                    wire:model="jira.project_key"
                    id="jira_project_key"
                    type="text"
                    placeholder="PROJ"
                    class="input-dark mt-1.5 @error('jira.project_key') input-dark-error @enderror"
                />
                @error('jira.project_key') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-2 border-t border-white/10 pt-4">
                <button
                    type="button"
                    wire:click="save('jira')"
                    wire:loading.attr="disabled"
                    wire:target="save('jira')"
                    class="btn-primary flex-1"
                >
                    <svg wire:loading wire:target="save('jira')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save('jira')">Salvar</span>
                    <span wire:loading wire:target="save('jira')">Salvando…</span>
                </button>
                <button
                    type="button"
                    wire:click="testConnection('jira')"
                    wire:loading.attr="disabled"
                    wire:target="testConnection('jira')"
                    class="btn-secondary flex-1"
                >
                    <svg wire:loading wire:target="testConnection('jira')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="testConnection('jira')" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z" />
                    </svg>
                    <span wire:loading.remove wire:target="testConnection('jira')">Testar conexão</span>
                    <span wire:loading wire:target="testConnection('jira')">Testando…</span>
                </button>
            </div>
        </div>

        {{-- ==================== GITHUB ==================== --}}
        <div class="card space-y-5">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Logo GitHub --}}
                    <svg class="h-6 w-6 text-white" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M12 0C5.372 0 0 5.373 0 12c0 5.303 3.438 9.8 8.205 11.387.6.111.82-.26.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61-.546-1.387-1.333-1.756-1.333-1.756-1.09-.745.083-.73.083-.73 1.205.085 1.84 1.238 1.84 1.238 1.07 1.834 2.807 1.304 3.492.997.108-.775.418-1.305.762-1.605-2.665-.305-5.467-1.334-5.467-5.931 0-1.31.468-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.3 1.23A11.51 11.51 0 0112 5.803c1.02.005 2.047.138 3.006.404 2.29-1.552 3.296-1.23 3.296-1.23.654 1.652.243 2.873.12 3.176.77.84 1.234 1.911 1.234 3.221 0 4.61-2.807 5.624-5.479 5.921.43.372.814 1.103.814 2.222 0 1.606-.015 2.898-.015 3.293 0 .319.216.694.825.576C20.565 21.796 24 17.3 24 12c0-6.627-5.373-12-12-12z"/>
                    </svg>
                    <span class="font-semibold text-white">GitHub</span>
                </div>
                <x-badge tone="{{ $github['api_token'] ? 'success' : 'neutral' }}" label="{{ $github['api_token'] ? 'Configurado' : 'Não configurado' }}" />
            </div>

            {{-- Owner do repositório --}}
            <div>
                <label for="github_repo_owner" class="label-dark">Owner do repositório</label>
                <input
                    wire:model="github.repo_owner"
                    id="github_repo_owner"
                    type="text"
                    placeholder="minha-empresa"
                    class="input-dark mt-1.5 @error('github.repo_owner') input-dark-error @enderror"
                />
                @error('github.repo_owner') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Nome do repositório --}}
            <div>
                <label for="github_repo_name" class="label-dark">Nome do repositório</label>
                <input
                    wire:model="github.repo_name"
                    id="github_repo_name"
                    type="text"
                    placeholder="meu-projeto"
                    class="input-dark mt-1.5 @error('github.repo_name') input-dark-error @enderror"
                />
                @error('github.repo_name') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Token de acesso pessoal com toggle --}}
            <div x-data="{ showGithubToken: false }">
                <label for="github_api_token" class="label-dark">Token de acesso pessoal</label>
                <div class="relative mt-1.5">
                    <input
                        wire:model="github.api_token"
                        id="github_api_token"
                        :type="showGithubToken ? 'text' : 'password'"
                        placeholder="ghp_••••••••••••••••"
                        class="input-dark pr-10 @error('github.api_token') input-dark-error @enderror"
                    />
                    <button
                        type="button"
                        @click="showGithubToken = !showGithubToken"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-300 focus:outline-none"
                        :aria-label="showGithubToken ? 'Ocultar token' : 'Mostrar token'"
                    >
                        <svg x-show="!showGithubToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showGithubToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('github.api_token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-2 border-t border-white/10 pt-4">
                <button
                    type="button"
                    wire:click="save('github')"
                    wire:loading.attr="disabled"
                    wire:target="save('github')"
                    class="btn-primary flex-1"
                >
                    <svg wire:loading wire:target="save('github')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save('github')">Salvar</span>
                    <span wire:loading wire:target="save('github')">Salvando…</span>
                </button>
                <button
                    type="button"
                    wire:click="testConnection('github')"
                    wire:loading.attr="disabled"
                    wire:target="testConnection('github')"
                    class="btn-secondary flex-1"
                >
                    <svg wire:loading wire:target="testConnection('github')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="testConnection('github')" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z" />
                    </svg>
                    <span wire:loading.remove wire:target="testConnection('github')">Testar conexão</span>
                    <span wire:loading wire:target="testConnection('github')">Testando…</span>
                </button>
            </div>
        </div>

        {{-- ==================== GITLAB ==================== --}}
        <div class="card space-y-5">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    {{-- Logo GitLab --}}
                    <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M22.65 14.39L12 22.13 1.35 14.39a.84.84 0 01-.3-.94l1.22-3.78 2.44-7.51A.42.42 0 014.84 2a.43.43 0 01.58 0 .42.42 0 01.12.23l2.44 7.49h8.04l2.44-7.49a.42.42 0 01.12-.23.43.43 0 01.58 0 .42.42 0 01.12.23l2.44 7.51 1.22 3.78a.84.84 0 01-.29.88z" fill="#FC6D26"/>
                    </svg>
                    <span class="font-semibold text-white">GitLab</span>
                </div>
                <x-badge tone="{{ $gitlab['api_token'] ? 'success' : 'neutral' }}" label="{{ $gitlab['api_token'] ? 'Configurado' : 'Não configurado' }}" />
            </div>

            {{-- URL base --}}
            <div>
                <label for="gitlab_base_url" class="label-dark">URL base</label>
                <input
                    wire:model="gitlab.base_url"
                    id="gitlab_base_url"
                    type="url"
                    placeholder="https://gitlab.com"
                    class="input-dark mt-1.5 @error('gitlab.base_url') input-dark-error @enderror"
                />
                @error('gitlab.base_url') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- ID do projeto --}}
            <div>
                <label for="gitlab_project_id" class="label-dark">ID do projeto</label>
                <input
                    wire:model="gitlab.project_id"
                    id="gitlab_project_id"
                    type="text"
                    placeholder="12345678"
                    class="input-dark mt-1.5 @error('gitlab.project_id') input-dark-error @enderror"
                />
                @error('gitlab.project_id') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Token de acesso com toggle --}}
            <div x-data="{ showGitlabToken: false }">
                <label for="gitlab_api_token" class="label-dark">Token de acesso</label>
                <div class="relative mt-1.5">
                    <input
                        wire:model="gitlab.api_token"
                        id="gitlab_api_token"
                        :type="showGitlabToken ? 'text' : 'password'"
                        placeholder="glpat-••••••••••••••••"
                        class="input-dark pr-10 @error('gitlab.api_token') input-dark-error @enderror"
                    />
                    <button
                        type="button"
                        @click="showGitlabToken = !showGitlabToken"
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-500 hover:text-slate-300 focus:outline-none"
                        :aria-label="showGitlabToken ? 'Ocultar token' : 'Mostrar token'"
                    >
                        <svg x-show="!showGitlabToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <svg x-show="showGitlabToken" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" x-cloak>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                        </svg>
                    </button>
                </div>
                @error('gitlab.api_token') <p class="error-text">{{ $message }}</p> @enderror
            </div>

            {{-- Botões --}}
            <div class="flex items-center gap-2 border-t border-white/10 pt-4">
                <button
                    type="button"
                    wire:click="save('gitlab')"
                    wire:loading.attr="disabled"
                    wire:target="save('gitlab')"
                    class="btn-primary flex-1"
                >
                    <svg wire:loading wire:target="save('gitlab')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <span wire:loading.remove wire:target="save('gitlab')">Salvar</span>
                    <span wire:loading wire:target="save('gitlab')">Salvando…</span>
                </button>
                <button
                    type="button"
                    wire:click="testConnection('gitlab')"
                    wire:loading.attr="disabled"
                    wire:target="testConnection('gitlab')"
                    class="btn-secondary flex-1"
                >
                    <svg wire:loading wire:target="testConnection('gitlab')" class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" stroke-opacity="0.25"></circle>
                        <path d="M22 12a10 10 0 00-10-10" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                    </svg>
                    <svg wire:loading.remove wire:target="testConnection('gitlab')" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5.25 5.653c0-.856.917-1.398 1.667-.986l11.54 6.347a1.125 1.125 0 010 1.972l-11.54 6.347a1.125 1.125 0 01-1.667-.986V5.653z" />
                    </svg>
                    <span wire:loading.remove wire:target="testConnection('gitlab')">Testar conexão</span>
                    <span wire:loading wire:target="testConnection('gitlab')">Testando…</span>
                </button>
            </div>
        </div>

    </div>
</div>
