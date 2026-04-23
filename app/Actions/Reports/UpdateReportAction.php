<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Models\Report;

class UpdateReportAction
{
    public function handle(Report $report, array $data): Report
    {
        $report->update([
            'type'        => $data['type'],
            'title'       => $data['title'],
            'description' => $data['description'],
        ]);

        return $report->fresh();
    }
}
