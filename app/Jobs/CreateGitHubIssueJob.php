<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\IntegrationJobStatus;
use App\Models\IntegrationJob;
use App\Models\Report;
use App\Models\TenantIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

final class CreateGitHubIssueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $reportId,
        private readonly string $integrationJobId,
    ) {}

    public function handle(): void
    {
        $report = Report::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->with(['labels'])
            ->findOrFail($this->reportId);
        $job = IntegrationJob::findOrFail($this->integrationJobId);

        // Idempotency
        if ($report->external_issue_id !== null) {
            $job->update(['status' => IntegrationJobStatus::Done]);
            return;
        }

        $integration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $report->tenant_id)
            ->firstOrFail();
        $config = decrypt($integration->config); // decrypt HERE only

        $job->update(['status' => IntegrationJobStatus::Processing, 'attempts' => $this->attempts()]);

        $payload = [
            'title'  => $report->enriched_title ?? $report->title,
            'body'   => $report->enriched_description ?? $report->description,
            'labels' => $report->labels->pluck('name')->toArray(),
        ];

        $response = Http::withToken($config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("https://api.github.com/repos/{$config['owner']}/{$config['repo']}/issues", $payload);

        $response->throw();

        $issueNumber = (string) $response->json('number');
        $issueUrl    = $response->json('html_url');

        $report->update([
            'external_issue_id'  => $issueNumber,
            'external_issue_url' => $issueUrl,
            'external_platform'  => 'github',
        ]);

        $job->update([
            'status'           => IntegrationJobStatus::Done,
            'external_id'      => $issueNumber,
            'response_payload' => $response->json(),
            'completed_at'     => now(),
        ]);
    }

    public function failed(\Throwable $exception): void
    {
        IntegrationJob::where('id', $this->integrationJobId)->update([
            'status'        => IntegrationJobStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
        Log::channel('integrations')->error('GitHub issue creation failed', [
            'report_id' => $this->reportId,
            'error'     => $exception->getMessage(),
        ]);
    }
}
