<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\ReportStatus;
use App\Models\Report;

class PublishReportAction
{
    public function handle(Report $report): Report
    {
        if ($report->status !== ReportStatus::Approved) {
            throw new \LogicException('Only approved reports can be published for voting.');
        }

        $report->update([
            'status'       => ReportStatus::PublishedForVoting,
            'published_at' => now(),
        ]);

        return $report->fresh();
    }
}
