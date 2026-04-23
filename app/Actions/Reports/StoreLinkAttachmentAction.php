<?php declare(strict_types=1);

namespace App\Actions\Reports;

use App\Enums\AttachmentType;
use App\Models\Report;
use App\Models\ReportAttachment;

class StoreLinkAttachmentAction
{
    public function handle(Report $report, string $url): ReportAttachment
    {
        return ReportAttachment::create([
            'report_id' => $report->id,
            'type'      => AttachmentType::Link,
            'url'       => $url,
        ]);
    }
}
