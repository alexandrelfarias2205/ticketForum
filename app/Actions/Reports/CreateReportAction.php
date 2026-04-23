<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

class CreateReportAction
{
    public function handle(User $author, array $data): Report
    {
        return Report::create([
            'tenant_id'   => $author->tenant_id,
            'author_id'   => $author->id,
            'type'        => $data['type'],
            'title'       => $data['title'],
            'description' => $data['description'],
            'status'      => ReportStatus::PendingReview,
        ]);
    }
}
