<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Models\IntegrationJob;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\TenantIntegration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CreateGitLabIssueJob implements ShouldQueue
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
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)
            ->with(['labels'])
            ->findOrFail($this->reportId);

        /** @var IntegrationJob $job */
        $job = IntegrationJob::findOrFail($this->integrationJobId);

        // Idempotency: skip if already created
        if ($report->external_issue_id !== null) {
            $job->update(['status' => IntegrationJobStatus::Done]);
            return;
        }

        $config = $this->resolveConfig($report);

        $job->update([
            'status'   => IntegrationJobStatus::Processing,
            'attempts' => $this->attempts(),
        ]);

        $baseUrl   = rtrim((string) $config['base_url'], '/');
        $projectId = (string) $config['project_id'];
        $token     = (string) $config['token'];

        $payload = [
            'title'       => $report->enriched_title ?? $report->title,
            'description' => $report->enriched_description ?? $report->description,
            'labels'      => $report->labels->pluck('name')->implode(','),
        ];

        $response = Http::withHeaders(['PRIVATE-TOKEN' => $token])
            ->timeout(30)
            ->connectTimeout(10)
            ->asJson()
            ->post("{$baseUrl}/api/v4/projects/{$projectId}/issues", $payload);

        $response->throw();

        $issueIid = (string) $response->json('iid');
        $issueUrl = (string) $response->json('web_url');

        $report->update([
            'external_issue_id'  => $issueIid,
            'external_issue_url' => $issueUrl,
            'external_platform'  => ExternalPlatform::GitLab,
        ]);

        $job->update([
            'status'           => IntegrationJobStatus::Done,
            'external_id'      => $issueIid,
            'response_payload' => $response->json(),
            'completed_at'     => now(),
        ]);
    }

    /**
     * Look up the config for the report. Prefer ProductIntegration when the report
     * is product-scoped; fall back to legacy TenantIntegration otherwise.
     *
     * @return array<string, mixed>
     */
    private function resolveConfig(Report $report): array
    {
        if ($report->product_id !== null) {
            $integration = ProductIntegration::where('product_id', $report->product_id)
                ->where('platform', ExternalPlatform::GitLab->value)
                ->where('is_active', true)
                ->first();

            if ($integration !== null) {
                return $integration->decryptedConfig();
            }
        }

        $tenantIntegration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $report->tenant_id)
            ->firstOrFail();

        return decrypt($tenantIntegration->config);
    }

    public function failed(Throwable $exception): void
    {
        IntegrationJob::where('id', $this->integrationJobId)->update([
            'status'        => IntegrationJobStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);

        Log::channel('integrations')->error('GitLab issue creation failed', [
            'report_id' => $this->reportId,
            'error'     => $exception->getMessage(),
        ]);
    }
}
