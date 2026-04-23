<?php declare(strict_types=1);

namespace App\Http\Requests\Integrations;

use Illuminate\Foundation\Http\FormRequest;

final class StoreJiraConfigRequest extends FormRequest
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
            'email'       => ['required', 'email'],
            'api_token'   => ['required', 'string'],
            'base_url'    => ['required', 'url'],
            'project_key' => ['required', 'string', 'max:50'],
        ];
    }
}
