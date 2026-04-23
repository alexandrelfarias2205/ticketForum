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

final class CreateJiraIssueJob implements ShouldQueue
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
            'fields' => [
                'project'     => ['key' => $config['project_key']],
                'summary'     => $report->enriched_title ?? $report->title,
                'description' => [
                    'type'    => 'doc',
                    'version' => 1,
                    'content' => [[
                        'type'    => 'paragraph',
                        'content' => [['type' => 'text', 'text' => $report->enriched_description ?? $report->description]],
                    ]],
                ],
                'issuetype' => ['name' => $this->mapType($report->type->value)],
                'labels'    => $report->labels->pluck('name')->toArray(),
            ],
        ];

        $response = Http::withBasicAuth($config['email'], $config['api_token'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("{$config['base_url']}/rest/api/3/issue", $payload);

        $response->throw();

        $issueKey = $response->json('key');
        $issueUrl = "{$config['base_url']}/browse/{$issueKey}";

        $report->update([
            'external_issue_id'  => $issueKey,
            'external_issue_url' => $issueUrl,
            'external_platform'  => 'jira',
        ]);

        $job->update([
            'status'           => IntegrationJobStatus::Done,
            'external_id'      => $issueKey,
            'response_payload' => $response->json(),
            'completed_at'     => now(),
        ]);
    }

    private function mapType(string $type): string
    {
        return match ($type) {
            'bug'             => 'Bug',
            'improvement'     => 'Improvement',
            'feature_request' => 'Story',
            default           => 'Task',
        };
    }

    public function failed(\Throwable $exception): void
    {
        IntegrationJob::where('id', $this->integrationJobId)->update([
            'status'        => IntegrationJobStatus::Failed,
            'error_message' => $exception->getMessage(),
        ]);
        Log::channel('integrations')->error('Jira issue creation failed', [
            'report_id' => $this->reportId,
            'error'     => $exception->getMessage(),
        ]);
    }
}
