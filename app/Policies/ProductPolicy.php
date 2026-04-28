<?php declare(strict_types=1);

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Product;
use App\Models\User;

final class ProductPolicy
{
    /**
     * Anyone authenticated may list products.
     */
    public function viewAny(User $user): bool
    {
        return $user->role === UserRole::Root
            || $user->role === UserRole::TenantAdmin
            || $user->role === UserRole::TenantUser;
    }

    /**
     * Anyone authenticated may view a product.
     */
    public function view(User $user, Product $product): bool
    {
        return $user->role === UserRole::Root
            || $user->role === UserRole::TenantAdmin
            || $user->role === UserRole::TenantUser;
    }

    /**
     * Only root may create products.
     */
    public function create(User $user): bool
    {
        return $user->role === UserRole::Root;
    }

    /**
     * Only root may update products.
     */
    public function update(User $user, Product $product): bool
    {
        return $user->role === UserRole::Root;
    }

    /**
     * Only root may delete products.
     */
    public function delete(User $user, Product $product): bool
    {
        return $user->role === UserRole::Root;
    }
}
