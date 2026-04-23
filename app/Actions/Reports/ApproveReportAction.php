<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

class ApproveReportAction
{
    public function handle(Report $report, User $reviewer, array $data): Report
    {
        $report->update([
            'status'               => ReportStatus::Approved,
            'reviewer_id'          => $reviewer->id,
            'reviewed_at'          => now(),
            'enriched_title'       => $data['enriched_title'],
            'enriched_description' => $data['enriched_description'],
        ]);

        $report->labels()->sync($data['label_ids'] ?? []);

        return $report->load('labels');
    }
}
