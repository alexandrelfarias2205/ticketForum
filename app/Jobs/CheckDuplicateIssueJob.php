<?php declare(strict_types=1);

namespace App\Jobs;

use App\Actions\Integrations\DispatchIssueCreationAction;
use App\Enums\ExternalPlatform;
use App\Enums\ReportType;
use App\Events\DuplicateReportDetected;
use App\Models\AgentLog;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Services\AI\IssueSimilarityService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CheckDuplicateIssueJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        private readonly string $reportId,
    ) {}

    public function handle(IssueSimilarityService $similarity, DispatchIssueCreationAction $dispatcher): void
    {
        /** @var Report $report */
        $report = Report::withoutGlobalScope(TenantScope::class)->findOrFail($this->reportId);

        if ($report->type !== ReportType::Bug) {
            return;
        }

        $platform = $this->resolvePlatform($report);
        $config   = $platform !== null ? $this->resolveConfig($report, $platform) : null;

        $existingIssues = $platform !== null && $config !== null
            ? $this->fetchOpenIssues($platform, $config)
            : [];

        $decision = $similarity->findSimilar(
            $report->enriched_title ?? $report->title,
            $report->enriched_description ?? $report->description,
            $existingIssues,
        );

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'duplicate_check',
            'payload'   => [
                'is_duplicate'     => $decision['is_duplicate'],
                'matched_issue_id' => $decision['matched_issue_id'],
                'confidence'       => $decision['confidence'],
            ],
        ]);

        if ($decision['is_duplicate'] && $decision['matched_issue_id'] !== null) {
            $this->markDuplicate($report, $decision['matched_issue_id']);
            EnrichExistingIssueJob::dispatch($report->id, $decision['matched_issue_id'])->onQueue('integrations');
            event(new DuplicateReportDetected($report, $decision['matched_issue_id']));
            return;
        }

        $dispatcher->handle($report);
    }

    private function markDuplicate(Report $report, string $matchedExternalId): void
    {
        // Look up the prior local report (same tenant) referencing the same external issue, if any.
        $original = Report::withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $report->tenant_id)
            ->where('external_issue_id', $matchedExternalId)
            ->where('id', '!=', $report->id)
            ->first();

        $report->update([
            'is_duplicate'           => true,
            'duplicate_of_report_id' => $original?->id,
        ]);
    }

    private function resolvePlatform(Report $report): ?ExternalPlatform
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
     * @return array<int, array{id: string, title: string, description?: string}>
     */
    private function fetchOpenIssues(ExternalPlatform $platform, array $config): array
    {
        try {
            return match ($platform) {
                ExternalPlatform::Jira   => $this->fetchJira($config),
                ExternalPlatform::GitHub => $this->fetchGitHub($config),
                ExternalPlatform::GitLab => $this->fetchGitLab($config),
            };
        } catch (Throwable $e) {
            Log::channel('integrations')->error('Open-issue fetch failed for duplicate check', [
                'platform' => $platform->value,
                'error'    => $e->getMessage(),
            ]);

            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{id: string, title: string, description?: string}>
     */
    private function fetchJira(array $config): array
    {
        $baseUrl    = rtrim((string) $config['base_url'], '/');
        $projectKey = (string) $config['project_key'];

        $response = Http::withBasicAuth((string) $config['email'], (string) $config['api_token'])
            ->timeout(15)
            ->get("{$baseUrl}/rest/api/3/search", [
                'jql'        => "project = {$projectKey} AND statusCategory != Done",
                'fields'     => 'summary,description',
                'maxResults' => 50,
            ]);

        $response->throw();

        $issues = $response->json('issues') ?? [];

        return collect($issues)->map(fn (array $i): array => [
            'id'          => (string) ($i['key'] ?? ''),
            'title'       => (string) ($i['fields']['summary'] ?? ''),
            'description' => '',
        ])->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{id: string, title: string, description?: string}>
     */
    private function fetchGitHub(array $config): array
    {
        $owner = (string) $config['owner'];
        $repo  = (string) $config['repo'];

        $response = Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(15)
            ->get("https://api.github.com/repos/{$owner}/{$repo}/issues", [
                'state'    => 'open',
                'per_page' => 50,
            ]);

        $response->throw();

        return collect($response->json() ?? [])
            ->filter(fn (array $i): bool => ! isset($i['pull_request'])) // exclude PRs
            ->map(fn (array $i): array => [
                'id'          => (string) ($i['number'] ?? ''),
                'title'       => (string) ($i['title'] ?? ''),
                'description' => (string) ($i['body'] ?? ''),
            ])->values()->all();
    }

    /**
     * @param  array<string, mixed>  $config
     * @return array<int, array{id: string, title: string, description?: string}>
     */
    private function fetchGitLab(array $config): array
    {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) $config['project_id'];

        $response = Http::withHeaders(['PRIVATE-TOKEN' => (string) $config['token']])
            ->timeout(15)
            ->get("{$baseUrl}/api/v4/projects/{$projectId}/issues", [
                'state'    => 'opened',
                'per_page' => 50,
            ]);

        $response->throw();

        return collect($response->json() ?? [])->map(fn (array $i): array => [
            'id'          => (string) ($i['iid'] ?? ''),
            'title'       => (string) ($i['title'] ?? ''),
            'description' => (string) ($i['description'] ?? ''),
        ])->values()->all();
    }
}
