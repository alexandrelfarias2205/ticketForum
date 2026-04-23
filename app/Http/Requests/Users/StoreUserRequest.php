<?php declare(strict_types=1);

namespace App\Http\Requests\Users;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user->role->isRoot() || $user->role->isTenantAdmin();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name'                  => ['required', 'string', 'max:255'],
            'email'                 => ['required', 'email', 'unique:users,email'],
            'password'              => ['required', 'string', 'min:12', 'confirmed'],
            'role'                  => ['required', 'in:tenant_admin,tenant_user'],
        ];
    }
}
