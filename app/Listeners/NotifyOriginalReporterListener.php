<?php declare(strict_types=1);

namespace App\Listeners;

use App\Events\DuplicateReportDetected;
use App\Notifications\DuplicateReportNotification;
use Illuminate\Contracts\Queue\ShouldQueue;

final class NotifyOriginalReporterListener implements ShouldQueue
{
    public function handle(DuplicateReportDetected $event): void
    {
        $author = $event->report->author;
        if ($author === null) {
            return;
        }

        $author->notify(new DuplicateReportNotification($event->report, $event->matchedIssueId));
    }
}
