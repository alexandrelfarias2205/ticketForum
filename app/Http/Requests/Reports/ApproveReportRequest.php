<?php declare(strict_types=1);

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class ApproveReportRequest extends FormRequest
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
            'enriched_title'       => ['required', 'string', 'max:500'],
            'enriched_description' => ['required', 'string'],
            'label_ids'            => ['array'],
            'label_ids.*'          => ['exists:labels,id'],
        ];
    }
}
