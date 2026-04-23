<?php declare(strict_types=1);

namespace App\Http\Requests\Labels;

use Illuminate\Foundation\Http\FormRequest;

class UpdateLabelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->role->isRoot();
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $labelId = $this->route('label')?->id ?? $this->route('label');

        return [
            'name'  => ['required', 'string', 'max:100', "unique:labels,name,{$labelId}"],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ];
    }
}
