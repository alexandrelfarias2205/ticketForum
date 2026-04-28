<?php declare(strict_types=1);

namespace App\Actions\Agent;

use App\Enums\ExternalPlatform;
use App\Enums\ReportStatus;
use App\Jobs\PollPipelineStatusJob;
use App\Models\AgentLog;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Services\AI\BugRiskAnalysisService;
use App\Services\Git\GitPushService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class AnalyzeAndFixBugAction
{
    public function __construct(
        private readonly BugRiskAnalysisService $riskService,
        private readonly GitPushService $gitPushService,
    ) {}

    public function handle(Report $report): void
    {
        // 1. Load other in-progress reports (bypassing tenant scope for agent context).
        $pendingReports = Report::withoutGlobalScope(TenantScope::class)
            ->where('status', ReportStatus::InProgress)
            ->where('id', '!=', $report->id)
            ->get();

        // 2. Risk analysis.
        $analysis = $this->riskService->analyze($report, $pendingReports);

        // 3. Log risk analysis.
        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'risk_analysis',
            'payload'   => $analysis,
        ]);

        // 4. Bail out if not safe.
        if (! $analysis['safe_to_proceed']) {
            AgentLog::create([
                'report_id' => $report->id,
                'action'    => 'skipped',
                'payload'   => ['reason' => $analysis['reasoning']],
            ]);
            return;
        }

        // 5. Generate branch name.
        $slugBase   = $report->external_issue_id ?? $report->id;
        $titleSlug  = Str::slug(mb_substr($report->title, 0, 30));
        $branchName = 'fix/' . Str::slug((string) $slugBase) . '-' . $titleSlug;

        // 6. Generate fix plan via Claude.
        $fixPlan = $this->generateFixPlan($report);

        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'fix_plan_generated',
            'payload'   => $fixPlan,
        ]);

        // 7. Resolve the product integration for git operations.
        $integration = $this->resolveIntegration($report);

        if ($integration === null) {
            AgentLog::create([
                'report_id' => $report->id,
                'action'    => 'branch_failed',
                'payload'   => ['reason' => 'No active git integration found for this report.'],
            ]);
            return;
        }

        // Create the branch.
        try {
            $this->gitPushService->createBranch($integration, $branchName);
        } catch (Throwable $e) {
            Log::channel('integrations')->error('AnalyzeAndFixBugAction: branch creation failed', [
                'report_id' => $report->id,
                'branch'    => $branchName,
                'error'     => $e->getMessage(),
            ]);

            AgentLog::create([
                'report_id' => $report->id,
                'action'    => 'branch_failed',
                'payload'   => ['reason' => $e->getMessage()],
            ]);
            return;
        }

        // 8. Persist the branch on the report.
        $report->agent_branch = $branchName;
        $report->save();

        // 9. Log branch creation.
        AgentLog::create([
            'report_id' => $report->id,
            'action'    => 'branch_created',
            'payload'   => ['branch' => $branchName],
        ]);

        // 10. Dispatch pipeline polling.
        PollPipelineStatusJob::dispatch($report->id, $branchName)->onQueue('integrations');
    }

    /**
     * @return array{files_to_modify: array<int, string>, fix_description: string, test_description: string}
     */
    private function generateFixPlan(Report $report): array
    {
        $apiKey = (string) config('services.anthropic.api_key');

        if ($apiKey === '') {
            return [
                'files_to_modify'  => [],
                'fix_description'  => 'API unavailable — fix plan skipped.',
                'test_description' => '',
            ];
        }

        $payload = [
            'model'      => 'claude-sonnet-4-6',
            'max_tokens' => 512,
            'system'     => [
                [
                    'type' => 'text',
                    'text' => "You are a senior software engineer reviewing a bug report. "
                        . "Plan the minimal fix required without executing any code. "
                        . "Reply ONLY with a single JSON object (no extra text, no code fences):\n"
                        . '{"files_to_modify":["..."],"fix_description":"...","test_description":"..."}',
                    'cache_control' => ['type' => 'ephemeral'],
                ],
            ],
            'messages' => [[
                'role'    => 'user',
                'content' => "BUG REPORT:\nTitle: {$report->title}\nDescription: {$report->description}\n\nProvide the fix plan JSON now.",
            ]],
        ];

        try {
            $response = Http::withHeaders([
                'x-api-key'         => $apiKey,
                'anthropic-version' => (string) config('services.anthropic.version', '2023-06-01'),
                'content-type'      => 'application/json',
            ])
                ->timeout(30)
                ->connectTimeout(10)
                ->post((string) config('services.anthropic.api_url'), $payload);

            $response->throw();
        } catch (Throwable $e) {
            Log::channel('integrations')->error('AnalyzeAndFixBugAction: fix plan generation failed', [
                'report_id' => $report->id,
                'error'     => $e->getMessage(),
            ]);

            return [
                'files_to_modify'  => [],
                'fix_description'  => 'Fix plan generation failed: ' . $e->getMessage(),
                'test_description' => '',
            ];
        }

        $blocks = $response->json('content') ?? [];
        $text   = '';
        foreach ((array) $blocks as $block) {
            if (is_array($block) && ($block['type'] ?? '') === 'text') {
                $text = (string) ($block['text'] ?? '');
                break;
            }
        }

        $text = trim(preg_replace('/^```(?:json)?\s*|\s*```$/m', '', $text) ?? $text);

        try {
            $decoded = json_decode($text, true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            $decoded = [];
        }

        if (! is_array($decoded)) {
            $decoded = [];
        }

        return [
            'files_to_modify'  => is_array($decoded['files_to_modify'] ?? null)
                ? array_values(array_map('strval', $decoded['files_to_modify']))
                : [],
            'fix_description'  => (string) ($decoded['fix_description'] ?? ''),
            'test_description' => (string) ($decoded['test_description'] ?? ''),
        ];
    }

    private function resolveIntegration(Report $report): ?ProductIntegration
    {
        if ($report->product_id === null) {
            return null;
        }

        $platform = $report->external_platform;

        if ($platform instanceof ExternalPlatform && $platform !== ExternalPlatform::Jira) {
            return ProductIntegration::where('product_id', $report->product_id)
                ->where('platform', $platform->value)
                ->where('is_active', true)
                ->first();
        }

        // Fall back to any active git integration for the product.
        return ProductIntegration::where('product_id', $report->product_id)
            ->whereIn('platform', [ExternalPlatform::GitHub->value, ExternalPlatform::GitLab->value])
            ->where('is_active', true)
            ->first();
    }
}
