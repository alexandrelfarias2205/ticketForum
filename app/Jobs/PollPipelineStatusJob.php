<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Events\PipelineFailed;
use App\Events\PipelineSucceeded;
use App\Models\AgentLog;
use App\Models\IntegrationJob;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class PollPipelineStatusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 60;

    /** Maximum poll attempts before giving up. */
    private const MAX_POLLS = 30;

    /** Seconds between polls. */
    private const POLL_DELAY = 120;

    public function __construct(
        private readonly string $reportId,
        private readonly string $branch,
        private readonly int $pollAttempt = 1,
    ) {}

    public function handle(): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)->findOrFail($this->reportId);

        $platform = $report->external_platform;
        if (! $platform instanceof ExternalPlatform || $platform === ExternalPlatform::Jira) {
            // Jira is not a CI provider — nothing to poll.
            return;
        }

        $config = $this->resolveConfig($report, $platform);
        if ($config === null) {
            return;
        }

        try {
            $status = match ($platform) {
                ExternalPlatform::GitHub => $this->fetchGitHubStatus($config),
                ExternalPlatform::GitLab => $this->fetchGitLabStatus($config),
                default                  => 'unknown',
            };
        } catch (Throwable $e) {
            Log::channel('integrations')->error('Pipeline status fetch failed', [
                'report_id' => $this->reportId,
                'branch'    => $this->branch,
                'error'     => $e->getMessage(),
            ]);
            $status = 'unknown';
        }

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'pipeline_poll',
            'payload'   => [
                'attempt'  => $this->pollAttempt,
                'branch'   => $this->branch,
                'status'   => $status,
                'platform' => $platform->value,
            ],
        ]);

        match ($status) {
            'success' => $this->handleSuccess($report),
            'failed'  => $this->handleFailure($report, 'Pipeline reported failed status'),
            default   => $this->scheduleNextPoll($report),
        };
    }

    private function handleSuccess(Report $report): void
    {
        IntegrationJob::where('report_id', $report->id)
            ->latest('id')
            ->limit(1)
            ->update(['status' => IntegrationJobStatus::Done]);

        OpenMergeRequestJob::dispatch($report->id, $this->branch)->onQueue('integrations');

        event(new PipelineSucceeded($report, ''));
    }

    private function handleFailure(Report $report, string $reason): void
    {
        IntegrationJob::create([
            'report_id'     => $report->id,
            'platform'      => $report->external_platform?->value ?? '',
            'status'        => IntegrationJobStatus::Failed,
            'error_message' => 'pipeline_failed: ' . $reason,
        ]);

        event(new PipelineFailed($report, $reason));
    }

    private function scheduleNextPoll(Report $report): void
    {
        if ($this->pollAttempt >= self::MAX_POLLS) {
            $this->handleFailure($report, 'Pipeline poll timeout (max attempts reached)');
            return;
        }

        self::dispatch($report->id, $this->branch, $this->pollAttempt + 1)
            ->onQueue('integrations')
            ->delay(now()->addSeconds(self::POLL_DELAY));
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveConfig(Report $report, ExternalPlatform $platform): ?array
    {
        if ($report->product_id === null) {
            return null;
        }

        $integration = ProductIntegration::where('product_id', $report->product_id)
            ->where('platform', $platform->value)
            ->where('is_active', true)
            ->first();

        return $integration?->decryptedConfig();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function fetchGitHubStatus(array $config): string
    {
        $owner = (string) ($config['owner'] ?? '');
        $repo  = (string) ($config['repo'] ?? '');

        $response = Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->get("https://api.github.com/repos/{$owner}/{$repo}/actions/runs", [
                'branch'   => $this->branch,
                'per_page' => 1,
            ]);

        $response->throw();
        $run = $response->json('workflow_runs.0');
        if ($run === null) {
            return 'unknown';
        }

        // GitHub: status (queued|in_progress|completed) + conclusion (success|failure|...)
        $runStatus  = (string) ($run['status'] ?? 'unknown');
        $conclusion = (string) ($run['conclusion'] ?? '');

        if ($runStatus === 'completed') {
            return $conclusion === 'success' ? 'success' : 'failed';
        }

        return $runStatus === 'in_progress' ? 'running' : 'queued';
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function fetchGitLabStatus(array $config): string
    {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) ($config['project_id'] ?? '');
        $token     = (string) ($config['token'] ?? '');

        $response = Http::withHeaders(['PRIVATE-TOKEN' => $token])
            ->timeout(30)
            ->connectTimeout(10)
            ->get("{$baseUrl}/api/v4/projects/{$projectId}/pipelines", [
                'ref'      => $this->branch,
                'per_page' => 1,
            ]);

        $response->throw();
        $pipeline = $response->json('0');
        if ($pipeline === null) {
            return 'unknown';
        }

        // GitLab pipeline statuses: created, waiting_for_resource, preparing, pending, running,
        // success, failed, canceled, skipped, manual, scheduled.
        return match ((string) ($pipeline['status'] ?? '')) {
            'success'                                                                                => 'success',
            'failed', 'canceled'                                                                     => 'failed',
            'running'                                                                                => 'running',
            'created', 'waiting_for_resource', 'preparing', 'pending', 'scheduled', 'manual'         => 'queued',
            default                                                                                  => 'unknown',
        };
    }
}
