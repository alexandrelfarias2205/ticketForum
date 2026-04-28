<?php declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

final class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::TenantAdmin
            || $user->role === UserRole::TenantUser;
    }

    public function view(User $user, Product $product): bool
    {
        return ($user->role === UserRole::TenantAdmin || $user->role === UserRole::TenantUser)
            && $user->tenant_id === $product->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->role === UserRole::TenantAdmin;
    }

    public function update(User $user, Product $product): bool
    {
        return $user->role === UserRole::TenantAdmin
            && $user->tenant_id === $product->tenant_id;
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->role === UserRole::TenantAdmin
            && $user->tenant_id === $product->tenant_id;
    }
}
