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
     * Resolve the platform from the product's active integration.
     * Integrations are now configured per product (by root), not per tenant.
     */
    private function resolvePlatform(Report $report): ExternalPlatform
    {
        if ($report->product_id === null) {
            throw new RuntimeException('Report has no product — cannot resolve integration.');
        }

        $integration = ProductIntegration::where('product_id', $report->product_id)
            ->where('is_active', true)
            ->first();

        if ($integration === null) {
            throw new RuntimeException('No active integration configured for this product.');
        }

        return $integration->platform;
    }
}
