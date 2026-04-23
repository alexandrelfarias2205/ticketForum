<?php declare(strict_types=1);

namespace App\Http\Requests\Reports;

use App\Enums\UserRole;
use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        /** @var \App\Models\User $user */
        $user = $this->user();

        return $user->role !== UserRole::Root;
    }

    public function rules(): array
    {
        return [
            'type'        => ['required', 'string', 'in:bug,improvement,feature_request'],
            'title'       => ['required', 'string', 'max:500'],
            'description' => ['required', 'string'],
        ];
    }
}
