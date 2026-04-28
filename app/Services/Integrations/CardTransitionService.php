<?php declare(strict_types=1);

namespace App\Services\Integrations;

use App\Enums\ExternalPlatform;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\TenantIntegration;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

final class CardTransitionService
{
    public function transitionToCodeReview(Report $report): void
    {
        if ($report->external_issue_id === null) {
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

        try {
            match ($platform) {
                ExternalPlatform::Jira   => $this->transitionJira($report, $config),
                ExternalPlatform::GitHub => $this->transitionGitHub($report, $config),
                ExternalPlatform::GitLab => $this->transitionGitLab($report, $config),
            };
        } catch (Throwable $e) {
            Log::warning('CardTransitionService: failed to transition card to code review', [
                'report_id'        => $report->id,
                'platform'         => $platform->value,
                'external_issue_id' => $report->external_issue_id,
                'error'            => $e->getMessage(),
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function transitionJira(Report $report, array $config): void
    {
        $baseUrl = rtrim((string) ($config['base_url'] ?? ''), '/');
        $issueId = (string) $report->external_issue_id;

        $transitionsResponse = Http::withBasicAuth((string) $config['email'], (string) $config['api_token'])
            ->timeout(30)
            ->connectTimeout(10)
            ->get("{$baseUrl}/rest/api/3/issue/{$issueId}/transitions");

        $transitionsResponse->throw();

        $transitionId = null;
        foreach ((array) $transitionsResponse->json('transitions') as $transition) {
            $name = strtolower((string) ($transition['name'] ?? ''));
            if ($name === 'code review' || $name === 'in review') {
                $transitionId = (string) ($transition['id'] ?? '');
                break;
            }
        }

        if ($transitionId === null) {
            Log::warning('CardTransitionService: Jira "Code Review" or "In Review" transition not found', [
                'report_id' => $report->id,
                'issue_id'  => $issueId,
            ]);
            return;
        }

        Http::withBasicAuth((string) $config['email'], (string) $config['api_token'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("{$baseUrl}/rest/api/3/issue/{$issueId}/transitions", [
                'transition' => ['id' => $transitionId],
            ])
            ->throw();
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function transitionGitHub(Report $report, array $config): void
    {
        $owner  = (string) ($config['owner'] ?? '');
        $repo   = (string) ($config['repo'] ?? '');
        $number = (string) $report->external_issue_id;

        Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->post("https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}/labels", [
                'labels' => ['code-review'],
            ])
            ->throw();

        // Remove "in-progress" label — ignore 404 (label may not be present)
        $removeResponse = Http::withToken((string) $config['token'])
            ->withHeaders(['Accept' => 'application/vnd.github+json'])
            ->timeout(30)
            ->connectTimeout(10)
            ->delete("https://api.github.com/repos/{$owner}/{$repo}/issues/{$number}/labels/in-progress");

        if (! $removeResponse->successful() && $removeResponse->status() !== 404) {
            $removeResponse->throw();
        }
    }

    /**
     * @param  array<string, mixed>  $config
     */
    private function transitionGitLab(Report $report, array $config): void
    {
        $baseUrl   = rtrim((string) ($config['base_url'] ?? 'https://gitlab.com'), '/');
        $projectId = (string) ($config['project_id'] ?? '');
        $iid       = (string) $report->external_issue_id;

        Http::withHeaders(['PRIVATE-TOKEN' => (string) $config['token']])
            ->timeout(30)
            ->connectTimeout(10)
            ->put("{$baseUrl}/api/v4/projects/{$projectId}/issues/{$iid}", [
                'add_labels'    => 'code-review',
                'remove_labels' => 'in-progress',
            ])
            ->throw();
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
}
