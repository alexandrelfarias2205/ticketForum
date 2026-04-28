<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Settings;

use App\Enums\ExternalPlatform;
use App\Models\TenantIntegration;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Http;
use Livewire\Component;

final class IntegrationSettings extends Component
{
    public array $jira = [
        'base_url'    => '',
        'project_key' => '',
        'api_token'   => '',
        'user_email'  => '',
    ];

    public array $github = [
        'repo_owner' => '',
        'repo_name'  => '',
        'api_token'  => '',
    ];

    public array $gitlab = [
        'base_url'   => '',
        'project_id' => '',
        'api_token'  => '',
    ];

    public function mount(): void
    {
        $this->authorize('update', auth()->user()->tenant);

        $tenantId = auth()->user()->tenant_id;

        $integrations = TenantIntegration::query()
            ->where('tenant_id', $tenantId)
            ->get()
            ->keyBy(fn (TenantIntegration $i): string => $i->platform->value);

        if (isset($integrations[ExternalPlatform::Jira->value])) {
            $config = json_decode(
                decrypt($integrations[ExternalPlatform::Jira->value]->config),
                true
            );
            $this->jira = array_merge($this->jira, $config ?? []);
        }

        if (isset($integrations[ExternalPlatform::GitHub->value])) {
            $config = json_decode(
                decrypt($integrations[ExternalPlatform::GitHub->value]->config),
                true
            );
            $this->github = array_merge($this->github, $config ?? []);
        }

        if (isset($integrations[ExternalPlatform::GitLab->value])) {
            $config = json_decode(
                decrypt($integrations[ExternalPlatform::GitLab->value]->config),
                true
            );
            $this->gitlab = array_merge($this->gitlab, $config ?? []);
        }
    }

    public function save(string $platform): void
    {
        $this->authorize('update', auth()->user()->tenant);

        $externalPlatform = ExternalPlatform::from($platform);

        [$rules, $config] = match ($externalPlatform) {
            ExternalPlatform::Jira => [
                [
                    'jira.base_url'    => 'required|url|max:255',
                    'jira.project_key' => 'required|string|max:50',
                    'jira.api_token'   => 'required|string|max:500',
                    'jira.user_email'  => 'required|email|max:255',
                ],
                $this->jira,
            ],
            ExternalPlatform::GitHub => [
                [
                    'github.repo_owner' => 'required|string|max:100',
                    'github.repo_name'  => 'required|string|max:100',
                    'github.api_token'  => 'required|string|max:500',
                ],
                $this->github,
            ],
            ExternalPlatform::GitLab => [
                [
                    'gitlab.base_url'   => 'required|url|max:255',
                    'gitlab.project_id' => 'required|string|max:100',
                    'gitlab.api_token'  => 'required|string|max:500',
                ],
                $this->gitlab,
            ],
        };

        $this->validate($rules);

        TenantIntegration::updateOrCreate(
            [
                'tenant_id' => auth()->user()->tenant_id,
                'platform'  => $externalPlatform,
            ],
            [
                'config'    => encrypt(json_encode($config)),
                'is_active' => true,
            ]
        );

        $this->dispatch('notify', type: 'success', message: 'Configuração salva com sucesso.');
    }

    public function testConnection(string $platform): void
    {
        $this->authorize('update', auth()->user()->tenant);

        $externalPlatform = ExternalPlatform::from($platform);

        try {
            $success = match ($externalPlatform) {
                ExternalPlatform::Jira => $this->testJiraConnection(),
                ExternalPlatform::GitHub => $this->testGitHubConnection(),
                ExternalPlatform::GitLab => $this->testGitLabConnection(),
            };

            if ($success) {
                $this->dispatch('notify', type: 'success', message: 'Conexão estabelecida com sucesso.');
            } else {
                $this->dispatch('notify', type: 'error', message: 'Falha na conexão. Verifique as credenciais.');
            }
        } catch (\Throwable $e) {
            $this->dispatch('notify', type: 'error', message: 'Erro ao testar conexão: ' . $e->getMessage());
        }
    }

    private function testJiraConnection(): bool
    {
        $baseUrl   = rtrim($this->jira['base_url'], '/');
        $userEmail = $this->jira['user_email'];
        $apiToken  = $this->jira['api_token'];

        $response = Http::timeout(5)
            ->withBasicAuth($userEmail, $apiToken)
            ->get("{$baseUrl}/rest/api/3/myself");

        return $response->successful();
    }

    private function testGitHubConnection(): bool
    {
        $response = Http::timeout(5)
            ->withToken($this->github['api_token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->get('https://api.github.com/user');

        return $response->successful();
    }

    private function testGitLabConnection(): bool
    {
        $baseUrl  = rtrim($this->gitlab['base_url'], '/');
        $apiToken = $this->gitlab['api_token'];

        $response = Http::timeout(5)
            ->withHeaders(['PRIVATE-TOKEN' => $apiToken])
            ->get("{$baseUrl}/api/v4/user");

        return $response->successful();
    }

    public function render(): View
    {
        return view('livewire.tenant.settings.integration-settings');
    }
}
