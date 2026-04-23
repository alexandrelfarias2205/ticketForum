<?php declare(strict_types=1);

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserAction
{
    public function handle(User $user, array $data): User
    {
        $payload = [
            'name'      => $data['name'],
            'email'     => $data['email'],
            'role'      => $data['role'],
            'is_active' => $data['is_active'] ?? $user->is_active,
        ];

        if (isset($data['password']) && $data['password'] !== '') {
            $payload['password'] = Hash::make($data['password']);
        }

        $user->update($payload);

        return $user->refresh();
    }
}
