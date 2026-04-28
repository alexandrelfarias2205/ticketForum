<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\AgentLog;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\User;
use App\Models\Vote;
use App\Services\AI\IssueSimilarityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

final class CheckDuplicateImprovementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    private const IMPROVEMENT_THRESHOLD = 0.80;

    public function __construct(
        private readonly string $reportId,
    ) {}

    public function handle(IssueSimilarityService $similarity): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)->findOrFail($this->reportId);

        if ($report->type !== ReportType::Improvement) {
            return;
        }

        $candidates = Report::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $report->tenant_id)
            ->where('type', ReportType::Improvement)
            ->where('status', ReportStatus::PublishedForVoting)
            ->where('id', '!=', $report->id)
            ->when($report->product_id !== null, fn ($q) => $q->where('product_id', $report->product_id))
            ->select(['id', 'title', 'description', 'enriched_title', 'enriched_description'])
            ->limit(50)
            ->get();

        if ($candidates->isEmpty()) {
            ModerateImprovementJob::dispatch($report->id)->onQueue('integrations');
            return;
        }

        $existingIssues = $candidates->map(fn (Report $c): array => [
            'id'          => $c->id,
            'title'       => $c->enriched_title ?? $c->title,
            'description' => $c->enriched_description ?? $c->description,
        ])->all();

        $decision = $similarity->findSimilar(
            $report->title,
            $report->description,
            $existingIssues,
        );

        $isDuplicate = $decision['is_duplicate'] && $decision['confidence'] >= self::IMPROVEMENT_THRESHOLD;

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'improvement_duplicate_check',
            'payload'   => [
                'is_duplicate'  => $isDuplicate,
                'matched_id'    => $decision['matched_issue_id'],
                'confidence'    => $decision['confidence'],
            ],
        ]);

        if ($isDuplicate && $decision['matched_issue_id'] !== null) {
            $this->autoVoteAndMarkDuplicate($report, $decision['matched_issue_id']);
            return;
        }

        ModerateImprovementJob::dispatch($report->id)->onQueue('integrations');
    }

    private function autoVoteAndMarkDuplicate(Report $duplicateReport, string $originalReportId): void
    {
        $original = Report::withoutGlobalScope(TenantScope::class)
            ->where('id', $originalReportId)
            ->where('tenant_id', $duplicateReport->tenant_id)
            ->first();

        if ($original === null) {
            ModerateImprovementJob::dispatch($duplicateReport->id)->onQueue('integrations');
            return;
        }

        $author = User::find($duplicateReport->author_id);
        if ($author !== null) {
            $alreadyVoted = Vote::where('user_id', $author->id)
                ->where('report_id', $original->id)
                ->exists();

            if (! $alreadyVoted) {
                DB::transaction(function () use ($author, $original): void {
                    Vote::create([
                        'user_id'   => $author->id,
                        'report_id' => $original->id,
                    ]);
                    Report::withoutGlobalScope(TenantScope::class)
                        ->where('id', $original->id)
                        ->increment('vote_count');
                });
            }
        }

        $duplicateReport->update([
            'is_duplicate'           => true,
            'duplicate_of_report_id' => $original->id,
            'status'                 => ReportStatus::Rejected,
            'rejection_reason'       => 'Sugestão duplicada — vinculada a uma melhoria existente em votação.',
            'reviewed_at'            => now(),
        ]);
    }
}
