<?php declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class CreateUserAction
{
    public function handle(array $data, ?Tenant $tenant = null): User
    {
        $tenantId = $tenant?->id ?? $data['tenant_id'] ?? null;

        return User::create([
            'id'        => Str::uuid()->toString(),
            'name'      => $data['name'],
            'email'     => $data['email'],
            'password'  => Hash::make($data['password']),
            'role'      => $data['role'],
            'tenant_id' => $tenantId,
            'is_active' => true,
        ]);
    }
}
