<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\AgentLog;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Services\AI\ImprovementModerationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class ModerateImprovementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $reportId,
    ) {}

    public function handle(ImprovementModerationService $moderation): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)->findOrFail($this->reportId);

        if ($report->type !== ReportType::Improvement) {
            return;
        }

        $verdict = $moderation->moderate($report->title, $report->description);

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'improvement_moderation',
            'payload'   => [
                'approved' => $verdict['approved'],
                'reason'   => $verdict['reason'],
            ],
        ]);

        if (! $verdict['approved']) {
            $report->update([
                'status'           => ReportStatus::Rejected,
                'rejection_reason' => $verdict['reason'] ?? 'Conteúdo rejeitado pela moderação automática.',
                'reviewed_at'      => now(),
            ]);
            return;
        }

        $report->update([
            'enriched_title'       => $verdict['cleaned_title'],
            'enriched_description' => $verdict['cleaned_description'],
            'status'               => ReportStatus::PublishedForVoting,
            'published_at'         => now(),
        ]);
    }
}
