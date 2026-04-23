<?php declare(strict_types=1);

namespace App\Actions\Integrations;

use App\Models\Tenant;
use App\Models\TenantIntegration;

final class SaveIntegrationConfigAction
{
    public function handle(Tenant $tenant, string $platform, array $config): TenantIntegration
    {
        $platformEnum = \App\Enums\ExternalPlatform::tryFrom($platform);
        if ($platformEnum === null) {
            throw new \InvalidArgumentException("Invalid platform: {$platform}");
        }

        /** @var TenantIntegration $integration */
        $integration = TenantIntegration::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'platform'   => $platformEnum,
                'config'     => encrypt($config),
                'is_active'  => true,
            ]
        );

        return $integration;
    }
}
