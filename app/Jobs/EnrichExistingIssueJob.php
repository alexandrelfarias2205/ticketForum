<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExternalPlatform;
use App\Models\AgentLog;
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

final class EnrichExistingIssueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $duplicateReportId,
        private readonly string $existingExternalIssueId,
    ) {}

    public function handle(): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)
            ->with(['author'])
            ->findOrFail($this->duplicateReportId);

        $platform = $report->external_platform ?? $this->guessPlatform($report);
        if (! $platform instanceof ExternalPlatform) {
            return;
        }

        $config = $this->resolveConfig($report, $platform);
        if ($config === null) {
            return;
        }

        $body = sprintf(
            "**Duplicate report received**\n\nReporter: %s\n\nTitle: %s\n\nDescription:\n%s",
            $report->author?->name ?? 'unknown',
            $report->title,
            $report->description,
        );

        try {
            match ($platform) {
                ExternalPlatform::Jira   => $this->commentJira($config, $body),
                ExternalPlatform::GitHub => $this->commentGitHub($config, $body),
                ExternalPlatform::GitLab => $this->commentGitLab($config, $body),
            };
        } catch (Throwable $e) {
            Log::channel('integrations')->error('EnrichExistingIssueJob failed', [
                'report_id' => $this->duplicateReportId,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }

        $report->update([
            'external_issue_id' => $this->existingExternalIssueId,
            'external_platform' => $platform,
        ]);

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'duplicate_enriched',
            'payload'   => [
                'matched_external_issue_id' => $this->existingExternalIssueId,
                'platform'                  => $platform->value,
            ],
        ]);
    }

    private function guessPlatform(Report $report): ?ExternalPlatform
    {
        if ($report->product_id === null) {
            return null;
        }

        $integration = ProductIntegration::where('product_id', $report->product_id)
            ->where('is_active', true)
            ->first();

        return $integration?->platform;
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
    private function commentJira(array $config, string $body): void
    {
        $baseUrl = rtrim((string) $config['base_url'], '/');
        Http::withBasicAuth((string) $config['email'], (string) $config['api_token'])
            ->timeout(30)
            ->connectTimeout(10)
            ->asJson()
            ->post("{$baseUrl}/rest/api/3/issue/{$this->existingExternalIssueId}/comment", [
                'body' => [
                    'type'    => 'doc',
                    'version' => 1,
                    'content' => [[
                        'type'    => 'paragraph',
                        'content' => [['type' => 'text', 'text' => $body]],
                    ]],
                ],
            ])
            ->throw();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function commentGitHub(array $config, string $body): void
    {
        Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post(
                "https://api.github.com/repos/{$config['owner']}/{$config['repo']}/issues/{$this->existingExternalIssueId}/comments",
                ['body' => $body],
            )
            ->throw();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function commentGitLab(array $config, string $body): void
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        Http::withHeaders(['PRIVATE-TOKEN' => (string) $config['token']])
            ->timeout(30)
            ->connectTimeout(10)
            ->asJson()
            ->post(
                "{$baseUrl}/api/v4/projects/{$config['project_id']}/issues/{$this->existingExternalIssueId}/notes",
                ['body' => $body],
            )
            ->throw();
    }
}
