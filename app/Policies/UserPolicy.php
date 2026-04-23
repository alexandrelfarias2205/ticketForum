<?php declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->isRoot() || $user->role->isTenantAdmin();
    }

    public function view(User $user, User $target): bool
    {
        if ($user->role->isRoot()) {
            return true;
        }

        return $user->role->isTenantAdmin() && $user->tenant_id === $target->tenant_id;
    }

    public function create(User $user): bool
    {
        return $user->role->isRoot() || $user->role->isTenantAdmin();
    }

    public function update(User $user, User $target): bool
    {
        if ($user->role->isRoot()) {
            return true;
        }

        return $user->role->isTenantAdmin()
            && $user->tenant_id === $target->tenant_id
            && ! $target->role->isRoot();
    }

    public function delete(User $user, User $target): bool
    {
        if ($user->role->isRoot()) {
            return true;
        }

        return $user->role->isTenantAdmin()
            && $user->tenant_id === $target->tenant_id
            && ! $target->role->isRoot()
            && $target->id !== $user->id;
    }
}
