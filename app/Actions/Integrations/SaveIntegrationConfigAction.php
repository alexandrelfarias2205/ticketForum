<?php declare(strict_types=1);

namespace App\Actions\Integrations;

use App\Models\Tenant;
use App\Models\TenantIntegration;
use Illuminate\Validation\ValidationException;

final class SaveIntegrationConfigAction
{
    public function handle(Tenant $tenant, string $platform, array $config): TenantIntegration
    {
        if (! in_array($platform, ['jira', 'github'], true)) {
            throw ValidationException::withMessages([
                'platform' => 'Platform must be jira or github.',
            ]);
        }

        /** @var TenantIntegration $integration */
        $integration = TenantIntegration::withoutGlobalScopes()->updateOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'platform'   => $platform,
                'config'     => encrypt($config),
                'is_active'  => true,
            ]
        );

        return $integration;
    }
}
