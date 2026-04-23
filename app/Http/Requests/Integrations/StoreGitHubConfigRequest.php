<?php declare(strict_types=1);

namespace App\Http\Requests\Integrations;

use Illuminate\Foundation\Http\FormRequest;

final class StoreGitHubConfigRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user->role->isRoot();
    }

    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'owner' => ['required', 'string', 'max:100'],
            'repo'  => ['required', 'string', 'max:100'],
        ];
    }
}
