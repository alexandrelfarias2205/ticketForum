<?php declare(strict_types=1);

namespace App\Livewire\Root\Integrations;

use App\Actions\Integrations\SaveIntegrationConfigAction;
use App\Models\Tenant;
use App\Models\TenantIntegration;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class IntegrationConfig extends Component
{
    public Tenant $tenant;
    public string $platform = 'jira';

    // Jira fields
    public string $jiraEmail      = '';
    public string $jiraApiToken   = '';
    public string $jiraBaseUrl    = '';
    public string $jiraProjectKey = '';

    // GitHub fields
    public string $githubToken = '';
    public string $githubOwner = '';
    public string $githubRepo  = '';

    public ?string $existingPlatform = null;

    public function mount(Tenant $tenant): void
    {
        $this->tenant = $tenant;

        $integration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->first();

        if ($integration !== null) {
            $this->existingPlatform = $integration->platform->value;
            $this->platform         = $integration->platform->value;

            $config = decrypt($integration->config);

            if ($integration->platform->value === 'jira') {
                $this->jiraEmail      = $config['email'] ?? '';
                $this->jiraApiToken   = $config['api_token'] ?? '';
                $this->jiraBaseUrl    = $config['base_url'] ?? '';
                $this->jiraProjectKey = $config['project_key'] ?? '';
            } elseif ($integration->platform->value === 'github') {
                $this->githubToken = $config['token'] ?? '';
                $this->githubOwner = $config['owner'] ?? '';
                $this->githubRepo  = $config['repo'] ?? '';
            }
        }
    }

    public function saveJira(SaveIntegrationConfigAction $action): void
    {
        $this->authorize('update', $this->tenant);

        $this->validate([
            'jiraEmail'      => ['required', 'email'],
            'jiraApiToken'   => ['required', 'string'],
            'jiraBaseUrl'    => ['required', 'url'],
            'jiraProjectKey' => ['required', 'string', 'max:50'],
        ]);

        $action->handle($this->tenant, 'jira', [
            'email'       => $this->jiraEmail,
            'api_token'   => $this->jiraApiToken,
            'base_url'    => $this->jiraBaseUrl,
            'project_key' => $this->jiraProjectKey,
        ]);

        $this->existingPlatform = 'jira';
        $this->dispatch('notify', message: 'Configuração do Jira salva com sucesso.', type: 'success');
    }

    public function saveGitHub(SaveIntegrationConfigAction $action): void
    {
        $this->authorize('update', $this->tenant);

        $this->validate([
            'githubToken' => ['required', 'string'],
            'githubOwner' => ['required', 'string', 'max:100'],
            'githubRepo'  => ['required', 'string', 'max:100'],
        ]);

        $action->handle($this->tenant, 'github', [
            'token' => $this->githubToken,
            'owner' => $this->githubOwner,
            'repo'  => $this->githubRepo,
        ]);

        $this->existingPlatform = 'github';
        $this->dispatch('notify', message: 'Configuração do GitHub salva com sucesso.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.root.integrations.integration-config');
    }
}
