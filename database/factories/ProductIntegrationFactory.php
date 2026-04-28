<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\ExternalPlatform;
use App\Models\Product;
use App\Models\ProductIntegration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductIntegrationFactory extends Factory
{
    protected $model = ProductIntegration::class;

    public function definition(): array
    {
        return [
            'id'         => Str::uuid(),
            'product_id' => Product::factory(),
            'platform'   => ExternalPlatform::GitHub,
            'config'     => encrypt([
                'token'      => 'fake-token-' . Str::random(20),
                'owner'      => 'acme-corp',
                'repo'       => 'sample-repo',
                'project_id' => 12345,
                'base_url'   => 'https://api.github.com',
            ]),
            'is_active'  => true,
        ];
    }

    public function forProduct(Product $product): static
    {
        return $this->state(['product_id' => $product->id]);
    }

    public function jira(): static
    {
        return $this->state([
            'platform' => ExternalPlatform::Jira,
            'config'   => encrypt([
                'base_url'    => 'https://example.atlassian.net',
                'email'       => 'agent@example.com',
                'api_token'   => 'fake-' . Str::random(20),
                'project_key' => 'PRJ',
            ]),
        ]);
    }

    public function github(): static
    {
        return $this->state([
            'platform' => ExternalPlatform::GitHub,
            'config'   => encrypt([
                'token'    => 'ghp_' . Str::random(36),
                'owner'    => 'acme-corp',
                'repo'     => 'sample-repo',
                'base_url' => 'https://api.github.com',
            ]),
        ]);
    }

    public function gitlab(): static
    {
        return $this->state([
            'platform' => ExternalPlatform::GitLab,
            'config'   => encrypt([
                'token'      => 'glpat-' . Str::random(20),
                'project_id' => 12345,
                'base_url'   => 'https://gitlab.com',
            ]),
        ]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
