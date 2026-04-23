<div>
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-800">Integration Config — {{ $tenant->name }}</h2>
        @if($existingPlatform)
            <p class="mt-1 text-sm text-green-600">Active integration: <strong>{{ ucfirst($existingPlatform) }}</strong></p>
        @else
            <p class="mt-1 text-sm text-gray-500">No active integration configured.</p>
        @endif
    </div>

    {{-- Tab navigation --}}
    <div class="mb-6 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <button
                wire:click="$set('platform', 'jira')"
                class="py-2 px-1 border-b-2 text-sm font-medium {{ $platform === 'jira' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                Jira
            </button>
            <button
                wire:click="$set('platform', 'github')"
                class="py-2 px-1 border-b-2 text-sm font-medium {{ $platform === 'github' ? 'border-indigo-500 text-indigo-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}"
            >
                GitHub Issues
            </button>
        </nav>
    </div>

    {{-- Jira Tab --}}
    @if($platform === 'jira')
        <form wire:submit="saveJira" class="space-y-5 max-w-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700">Atlassian Email</label>
                <input
                    type="email"
                    wire:model="jiraEmail"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="you@example.com"
                />
                @error('jiraEmail') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">API Token</label>
                <input
                    type="password"
                    wire:model="jiraApiToken"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="••••••••"
                />
                @error('jiraApiToken') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Base URL</label>
                <input
                    type="url"
                    wire:model="jiraBaseUrl"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="https://yourcompany.atlassian.net"
                />
                @error('jiraBaseUrl') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Project Key</label>
                <input
                    type="text"
                    wire:model="jiraProjectKey"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="PROJ"
                />
                @error('jiraProjectKey') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Save Jira Config
                </button>
            </div>
        </form>
    @endif

    {{-- GitHub Tab --}}
    @if($platform === 'github')
        <form wire:submit="saveGitHub" class="space-y-5 max-w-lg">
            <div>
                <label class="block text-sm font-medium text-gray-700">Personal Access Token</label>
                <input
                    type="password"
                    wire:model="githubToken"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="ghp_••••••••"
                />
                @error('githubToken') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Repository Owner</label>
                <input
                    type="text"
                    wire:model="githubOwner"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="acme-org"
                />
                @error('githubOwner') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Repository Name</label>
                <input
                    type="text"
                    wire:model="githubRepo"
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                    placeholder="my-repo"
                />
                @error('githubRepo') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <button
                    type="submit"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                    Save GitHub Config
                </button>
            </div>
        </form>
    @endif
</div>
