<?php declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->role->isRoot() || $user->role->isTenantAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'name'      => ['required', 'string', 'max:255'],
            'email'     => ['required', 'email', "unique:users,email,{$userId}"],
            'password'  => ['nullable', 'string', 'min:12', 'confirmed'],
            'role'      => ['required', 'in:tenant_admin,tenant_user'],
            'is_active' => ['boolean'],
        ];
    }
}
