<?php declare(strict_types=1);

namespace App\Actions\Integrations;

use App\Enums\IntegrationJobStatus;
use App\Jobs\CreateGitHubIssueJob;
use App\Jobs\CreateJiraIssueJob;
use App\Models\IntegrationJob;
use App\Models\Report;
use App\Models\TenantIntegration;

final class DispatchIssueCreationAction
{
    public function handle(Report $report): IntegrationJob
    {
        $integration = TenantIntegration::withoutGlobalScopes()
            ->where('tenant_id', $report->tenant_id)
            ->where('is_active', true)
            ->firstOrFail();

        /** @var IntegrationJob $integrationJob */
        $integrationJob = IntegrationJob::create([
            'report_id' => $report->id,
            'platform'  => $integration->platform->value,
            'status'    => IntegrationJobStatus::Pending,
        ]);

        match ($integration->platform->value) {
            'jira'   => CreateJiraIssueJob::dispatch($report->id, $integrationJob->id)->onQueue('integrations'),
            'github' => CreateGitHubIssueJob::dispatch($report->id, $integrationJob->id)->onQueue('integrations'),
        };

        return $integrationJob;
    }
}
