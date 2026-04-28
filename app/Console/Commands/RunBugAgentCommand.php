<?php declare(strict_types=1);

namespace App\Console\Commands;

use App\Actions\Agent\AnalyzeAndFixBugAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use Illuminate\Console\Command;
use Throwable;

final class RunBugAgentCommand extends Command
{
    protected $signature = 'agent:run-bugs {--limit=3 : Maximum number of reports to process}';

    protected $description = 'Process approved bug reports with the autonomous agent';

    public function handle(AnalyzeAndFixBugAction $action): int
    {
        $limit = (int) $this->option('limit');

        /** @var \Illuminate\Database\Eloquent\Collection<int, Report> $reports */
        $reports = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::InProgress)
            ->whereNotNull('external_issue_id')
            ->whereNull('agent_branch')
            ->orderByDesc('vote_count')
            ->orderBy('created_at')
            ->limit($limit)
            ->get();

        if ($reports->isEmpty()) {
            $this->info('No eligible bug reports found.');
            return Command::SUCCESS;
        }

        $this->info("Processing {$reports->count()} report(s)...");

        foreach ($reports as $report) {
            $this->info("[{$report->id}] Starting: {$report->title}");

            try {
                $action->handle($report);
                $this->info("[{$report->id}] Done.");
            } catch (Throwable $e) {
                $this->error("[{$report->id}] Failed: {$e->getMessage()}");
            }
        }

        return Command::SUCCESS;
    }
}
