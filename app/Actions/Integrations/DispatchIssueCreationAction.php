<?php declare(strict_types=1);

namespace App\Actions\Integrations;

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Jobs\CreateGitHubIssueJob;
use App\Jobs\CreateGitLabIssueJob;
use App\Jobs\CreateJiraIssueJob;
use App\Models\IntegrationJob;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\TenantIntegration;
use RuntimeException;

final class DispatchIssueCreationAction
{
    public function handle(Report $report): IntegrationJob
    {
        $platform = $this->resolvePlatform($report);

        /** @var IntegrationJob $integrationJob */
        $integrationJob = IntegrationJob::create([
            'report_id' => $report->id,
            'platform'  => $platform->value,
            'status'    => IntegrationJobStatus::Pending,
        ]);

        match ($platform) {
            ExternalPlatform::Jira   => CreateJiraIssueJob::dispatch($report->id, $integrationJob->id)->onQueue('integrations'),
            ExternalPlatform::GitHub => CreateGitHubIssueJob::dispatch($report->id, $integrationJob->id)->onQueue('integrations'),
            ExternalPlatform::GitLab => CreateGitLabIssueJob::dispatch($report->id, $integrationJob->id)->onQueue('integrations'),
        };

        return $integrationJob;
    }

    /**
     * Prefer ProductIntegration when the report belongs to a product; fall back to TenantIntegration.
     */
    private function resolvePlatform(Report $report): ExternalPlatform
    {
        if ($report->product_id !== null) {
            $integration = ProductIntegration::where('product_id', $report->product_id)
                ->where('is_active', true)
                ->first();

            if ($integration !== null) {
                return $integration->platform;
            }
        }

        $tenantIntegration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $report->tenant_id)
            ->where('is_active', true)
            ->first();

        if ($tenantIntegration === null) {
            throw new RuntimeException('No active integration configured for report.');
        }

        return $tenantIntegration->platform;
    }
}
