<?php declare(strict_types=1);

namespace App\Events;

use App\Models\Report;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class DuplicateReportDetected
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public readonly Report $report,
        public readonly string $matchedIssueId,
    ) {}
}
