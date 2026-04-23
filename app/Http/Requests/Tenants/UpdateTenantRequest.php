<?php declare(strict_types=1);

namespace App\Http\Requests\Tenants;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->isRoot();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'in:free,pro,enterprise'],
        ];
    }
}
