<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\User;

class RejectReportAction
{
    public function handle(Report $report, User $reviewer, string $reason): Report
    {
        $report->update([
            'status'           => ReportStatus::Rejected,
            'reviewer_id'      => $reviewer->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        return $report->fresh();
    }
}
