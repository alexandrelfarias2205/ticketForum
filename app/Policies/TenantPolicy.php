<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\Tenant;
use App\Models\User;

class TenantPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isRoot();
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $user->role->isRoot();
    }

    public function create(User $user): bool
    {
        return $user->role->isRoot();
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $user->role->isRoot();
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $user->role->isRoot();
    }
}
