<?php declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Str;

class UpdateTenantAction
{
    public function handle(Tenant $tenant, array $data): Tenant
    {
        $slug = isset($data['name']) && $data['name'] !== $tenant->name
            ? Str::slug($data['name'])
            : $tenant->slug;

        $tenant->update([
            'name'      => $data['name'] ?? $tenant->name,
            'slug'      => $slug,
            'plan'      => $data['plan'] ?? $tenant->plan,
            'is_active' => $data['is_active'] ?? $tenant->is_active,
        ]);

        return $tenant->refresh();
    }
}
