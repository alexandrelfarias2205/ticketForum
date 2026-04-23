<?php declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;

class DeactivateUserAction
{
    public function handle(User $user): void
    {
        $user->update(['is_active' => false]);
    }
}
