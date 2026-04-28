<?php declare(strict_types=1);

namespace App\Jobs;

use App\Enums\ExternalPlatform;
use App\Models\AgentLog;
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

final class OpenMergeRequestJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $reportId,
        private readonly string $branch,
    ) {}

    public function handle(): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)->findOrFail($this->reportId);

        if ($report->merge_request_url !== null) {
            // Idempotent: MR already opened.
            return;
        }

        $platform = $report->external_platform;
        if (! $platform instanceof ExternalPlatform) {
            return;
        }

        $config = $this->resolveConfig($report, $platform);
        if ($config === null) {
            return;
        }

        $title = sprintf('Fix #%s: %s', $report->external_issue_id ?? '', $report->enriched_title ?? $report->title);
        $body  = sprintf("Closes #%s\n\n%s", $report->external_issue_id ?? '', $report->enriched_description ?? $report->description);

        try {
            $mrUrl = match ($platform) {
                ExternalPlatform::GitHub => $this->openGitHubPullRequest($config, $title, $body),
                ExternalPlatform::GitLab => $this->openGitLabMergeRequest($config, $title, $body),
                default                  => null,
            };
        } catch (Throwable $e) {
            Log::channel('integrations')->error('Merge request opening failed', [
                'report_id' => $report->id,
                'branch'    => $this->branch,
                'error'     => $e->getMessage(),
            ]);
            throw $e;
        }

        if ($mrUrl !== null) {
            $report->update(['merge_request_url' => $mrUrl]);

            AgentLog::create([
                'report_id' => $report->id,
                'action'    => 'merge_request_opened',
                'payload'   => [
                    'branch'   => $this->branch,
                    'url'      => $mrUrl,
                    'platform' => $platform->value,
                ],
            ]);
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    private function resolveConfig(Report $report, ExternalPlatform $platform): ?array
    {
        if ($report->product_id !== null) {
            $integration = ProductIntegration::where('product_id', $report->product_id)
                ->where('platform', $platform->value)
                ->where('is_active', true)
                ->first();

            if ($integration !== null) {
                return $integration->decryptedConfig();
            }
        }

        $tenantIntegration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $report->tenant_id)
            ->first();

        return $tenantIntegration ? decrypt($tenantIntegration->config) : null;
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function openGitHubPullRequest(array $config, string $title, string $body): string
    {
        $owner = (string) ($config['owner'] ?? '');
        $repo  = (string) ($config['repo'] ?? '');
        $base  = (string) ($config['base_branch'] ?? 'main');

        $response = Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("https://api.github.com/repos/{$owner}/{$repo}/pulls", [
                'title' => $title,
                'body'  => $body,
                'head'  => $this->branch,
                'base'  => $base,
            ]);

        $response->throw();

        return (string) $response->json('html_url');
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function openGitLabMergeRequest(array $config, string $title, string $body): string
    {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) ($config['project_id'] ?? '');
        $target    = (string) ($config['target_branch'] ?? 'main');

        $response = Http::withHeaders(['PRIVATE-TOKEN' => (string) $config['token']])
            ->timeout(30)
            ->connectTimeout(10)
            ->asJson()
            ->post("{$baseUrl}/api/v4/projects/{$projectId}/merge_requests", [
                'source_branch' => $this->branch,
                'target_branch' => $target,
                'title'         => $title,
                'description'   => $body,
            ]);

        $response->throw();

        return (string) $response->json('web_url');
    }
}
