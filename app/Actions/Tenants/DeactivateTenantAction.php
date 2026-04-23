<?php declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Models\Tenant;

class DeactivateTenantAction
{
    public function handle(Tenant $tenant): void
    {
        $tenant->update(['is_active' => false]);

        $tenant->users()->update(['is_active' => false]);
    }
}
