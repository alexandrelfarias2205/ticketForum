<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Integrations\DispatchIssueCreationAction;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\AgentLog;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use Illuminate\Console\Command;
use Throwable;

final class PromoteTopImprovementsCommand extends Command
{
    protected $signature = 'improvements:promote {--limit=5 : Maximum number of improvements to promote}';

    protected $description = 'Promote the most-voted published improvements to in_progress and dispatch issue creation';

    public function handle(DispatchIssueCreationAction $dispatcher): int
    {
        $limit = max(1, (int) $this->option('limit'));

        $candidates = Report::withoutGlobalScope(TenantScope::class)
            ->where('type', ReportType::Improvement)
            ->where('status', ReportStatus::PublishedForVoting)
            ->where('is_duplicate', false)
            ->orderByDesc('vote_count')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($candidates->isEmpty()) {
            $this->info('No improvements eligible for promotion.');
            return self::SUCCESS;
        }

        $promoted = 0;

        foreach ($candidates as $report) {
            try {
                $dispatcher->handle($report);

                $report->update([
                    'status' => ReportStatus::InProgress,
                ]);

                AgentLog::create([
                    'report_id' => $report->id,
                    'action'    => 'improvement_promoted',
                    'payload'   => [
                        'vote_count' => $report->vote_count,
                    ],
                ]);

                $this->info("Promoted improvement {$report->id} (votes: {$report->vote_count}).");
                $promoted++;
            } catch (Throwable $e) {
                $this->error("Failed to promote {$report->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done. Promoted {$promoted}/{$candidates->count()} improvements.");

        return self::SUCCESS;
    }
}
