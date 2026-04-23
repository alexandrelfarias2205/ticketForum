<?php declare(strict_types=1);

namespace App\Actions\Tenants;

use App\Models\Tenant;
use Illuminate\Support\Str;

class CreateTenantAction
{
    public function handle(array $data): Tenant
    {
        return Tenant::create([
            'name'      => $data['name'],
            'slug'      => Str::slug($data['name']),
            'plan'      => $data['plan'] ?? \App\Enums\TenantPlan::Free,
            'is_active' => true,
        ]);
    }
}
