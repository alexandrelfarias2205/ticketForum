<?php declare(strict_types=1);

namespace App\Http\Requests\Reports;

use Illuminate\Foundation\Http\FormRequest;

class StoreReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Report::class);
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
