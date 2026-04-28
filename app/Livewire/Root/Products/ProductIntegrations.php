<?php declare(strict_types=1);

namespace App\Livewire\Root\Products;

use App\Enums\ExternalPlatform;
use App\Jobs\TestIntegrationJob;
use App\Models\Product;
use App\Models\ProductIntegration;
use Illuminate\Contracts\View\View;
use Livewire\Component;

final class ProductIntegrations extends Component
{
    public Product $product;

    /** @var array<string, string|bool> */
    public array $jira = [
        'base_url'    => '',
        'project_key' => '',
        'api_token'   => '',
        'email'       => '',
        'is_active'   => false,
    ];

    /** @var array<string, string|bool> */
    public array $github = [
        'owner'     => '',
        'repo'      => '',
        'token'     => '',
        'is_active' => false,
    ];

    /** @var array<string, string|bool> */
    public array $gitlab = [
        'base_url'   => '',
        'project_id' => '',
        'token'      => '',
        'is_active'  => false,
    ];

    public function mount(Product $product): void
    {
        $this->authorize('update', $product);

        $this->product = $product;

        $integrations = ProductIntegration::query()
            ->where('product_id', $product->id)
            ->get()
            ->keyBy(fn (ProductIntegration $i): string => $i->platform->value);

        if (isset($integrations[ExternalPlatform::Jira->value])) {
            $integration = $integrations[ExternalPlatform::Jira->value];
            $config      = $integration->decryptedConfig();
            $this->jira  = [
                'base_url'    => (string) ($config['base_url'] ?? ''),
                'project_key' => (string) ($config['project_key'] ?? ''),
                'api_token'   => (string) ($config['api_token'] ?? ''),
                'email'       => (string) ($config['email'] ?? ''),
                'is_active'   => (bool) $integration->is_active,
            ];
        }

        if (isset($integrations[ExternalPlatform::GitHub->value])) {
            $integration  = $integrations[ExternalPlatform::GitHub->value];
            $config       = $integration->decryptedConfig();
            $this->github = [
                'owner'     => (string) ($config['owner'] ?? ''),
                'repo'      => (string) ($config['repo'] ?? ''),
                'token'     => (string) ($config['token'] ?? ''),
                'is_active' => (bool) $integration->is_active,
            ];
        }

        if (isset($integrations[ExternalPlatform::GitLab->value])) {
            $integration  = $integrations[ExternalPlatform::GitLab->value];
            $config       = $integration->decryptedConfig();
            $this->gitlab = [
                'base_url'   => (string) ($config['base_url'] ?? ''),
                'project_id' => (string) ($config['project_id'] ?? ''),
                'token'      => (string) ($config['token'] ?? ''),
                'is_active'  => (bool) $integration->is_active,
            ];
        }
    }

    public function saveJira(): void
    {
        $this->authorize('update', $this->product);

        $this->validate([
            'jira.base_url'    => ['required', 'url', 'max:255'],
            'jira.project_key' => ['required', 'string', 'max:50'],
            'jira.api_token'   => ['required', 'string', 'max:500'],
            'jira.email'       => ['required', 'email', 'max:255'],
        ], [
            'jira.base_url.required'    => 'A URL base do Jira é obrigatória.',
            'jira.project_key.required' => 'A chave do projeto é obrigatória.',
            'jira.api_token.required'   => 'O token de API é obrigatório.',
            'jira.email.required'       => 'O e-mail é obrigatório.',
            'jira.base_url.url'         => 'Informe uma URL válida.',
            'jira.email.email'          => 'Informe um e-mail válido.',
        ]);

        ProductIntegration::updateOrCreate(
            [
                'product_id' => $this->product->id,
                'platform'   => ExternalPlatform::Jira,
            ],
            [
                'config' => encrypt([
                    'base_url'    => $this->jira['base_url'],
                    'project_key' => $this->jira['project_key'],
                    'api_token'   => $this->jira['api_token'],
                    'email'       => $this->jira['email'],
                ]),
                'is_active' => (bool) $this->jira['is_active'],
            ],
        );

        $this->dispatch('notify', type: 'success', message: 'Configuração do Jira salva.');
    }

    public function saveGitHub(): void
    {
        $this->authorize('update', $this->product);

        $this->validate([
            'github.owner' => ['required', 'string', 'max:100'],
            'github.repo'  => ['required', 'string', 'max:100'],
            'github.token' => ['required', 'string', 'max:500'],
        ], [
            'github.owner.required' => 'O owner do repositório é obrigatório.',
            'github.repo.required'  => 'O nome do repositório é obrigatório.',
            'github.token.required' => 'O token de API é obrigatório.',
        ]);

        ProductIntegration::updateOrCreate(
            [
                'product_id' => $this->product->id,
                'platform'   => ExternalPlatform::GitHub,
            ],
            [
                'config' => encrypt([
                    'owner' => $this->github['owner'],
                    'repo'  => $this->github['repo'],
                    'token' => $this->github['token'],
                ]),
                'is_active' => (bool) $this->github['is_active'],
            ],
        );

        $this->dispatch('notify', type: 'success', message: 'Configuração do GitHub salva.');
    }

    public function saveGitLab(): void
    {
        $this->authorize('update', $this->product);

        $this->validate([
            'gitlab.base_url'   => ['required', 'url', 'max:255'],
            'gitlab.project_id' => ['required', 'string', 'max:100'],
            'gitlab.token'      => ['required', 'string', 'max:500'],
        ], [
            'gitlab.base_url.required'   => 'A URL base do GitLab é obrigatória.',
            'gitlab.project_id.required' => 'O ID do projeto é obrigatório.',
            'gitlab.token.required'      => 'O token de API é obrigatório.',
            'gitlab.base_url.url'        => 'Informe uma URL válida.',
        ]);

        ProductIntegration::updateOrCreate(
            [
                'product_id' => $this->product->id,
                'platform'   => ExternalPlatform::GitLab,
            ],
            [
                'config' => encrypt([
                    'base_url'   => $this->gitlab['base_url'],
                    'project_id' => $this->gitlab['project_id'],
                    'token'      => $this->gitlab['token'],
                ]),
                'is_active' => (bool) $this->gitlab['is_active'],
            ],
        );

        $this->dispatch('notify', type: 'success', message: 'Configuração do GitLab salva.');
    }

    public function testConnection(string $platform): void
    {
        $this->authorize('update', $this->product);

        $platformEnum = ExternalPlatform::tryFrom($platform);
        if ($platformEnum === null) {
            $this->dispatch('notify', type: 'error', message: 'Plataforma desconhecida.');
            return;
        }

        $integration = ProductIntegration::query()
            ->where('product_id', $this->product->id)
            ->where('platform', $platformEnum->value)
            ->first();

        if ($integration === null) {
            $this->dispatch('notify', type: 'error', message: 'Salve a configuração antes de testar.');
            return;
        }

        TestIntegrationJob::dispatch((string) $integration->id)->onQueue('integrations');

        $this->dispatch('notify', type: 'success', message: 'Teste de conexão enfileirado. Verifique os logs em instantes.');
    }

    public function render(): View
    {
        return view('livewire.root.products.product-integrations')->layout('components.layouts.root');
    }
}
