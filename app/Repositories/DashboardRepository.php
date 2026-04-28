<?php declare(strict_types=1);

namespace App\Repositories;

use App\Enums\ExternalPlatform;
use App\Enums\IntegrationJobStatus;
use App\Enums\ReportStatus;
use App\Enums\ReportType;
use App\Models\IntegrationJob;
use App\Models\Product;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use App\Models\Vote;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon as SupportCarbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Aggregated read-only queries for the tenant dashboard.
 *
 * All methods accept the tenant_id explicitly and bypass the global
 * TenantScope so they can be invoked outside of an authenticated HTTP
 * context (jobs, tests). Aggregations are cached for 60 seconds; the
 * activity feed is intentionally uncached for real-time perception.
 */
final class DashboardRepository
{
    private const CACHE_TTL_SECONDS = 60;

    public function headerCards(string $tenantId): array
    {
        return Cache::remember(
            $this->cacheKey($tenantId, 'header_cards'),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->computeHeaderCards($tenantId),
        );
    }

    public function statusFunnel(string $tenantId): array
    {
        return Cache::remember(
            $this->cacheKey($tenantId, 'status_funnel'),
            self::CACHE_TTL_SECONDS,
            fn (): array => $this->computeStatusFunnel($tenantId),
        );
    }

    /**
     * Real-time activity feed — never cached.
     *
     * @return array<int, array{type: string, icon: string, message: string, created_at: CarbonInterface, report_id: string|null, report_title: string|null}>
     */
    public function activityFeed(string $tenantId, int $limit = 10): array
    {
        // 1. Recent agent log entries scoped via reports.tenant_id
        $agentRows = DB::table('agent_logs')
            ->join('reports', 'reports.id', '=', 'agent_logs.report_id')
            ->where('reports.tenant_id', $tenantId)
            ->whereNull('reports.deleted_at')
            ->orderByDesc('agent_logs.created_at')
            ->limit($limit)
            ->get([
                'agent_logs.action as action',
                'agent_logs.created_at as created_at',
                'agent_logs.report_id as report_id',
                'reports.title as report_title',
            ]);

        // 2. Recent integration jobs scoped via reports.tenant_id
        $integrationRows = DB::table('integration_jobs')
            ->join('reports', 'reports.id', '=', 'integration_jobs.report_id')
            ->where('reports.tenant_id', $tenantId)
            ->whereNull('reports.deleted_at')
            ->orderByDesc('integration_jobs.created_at')
            ->limit($limit)
            ->get([
                'integration_jobs.platform as platform',
                'integration_jobs.status as status',
                'integration_jobs.created_at as created_at',
                'integration_jobs.report_id as report_id',
                'reports.title as report_title',
            ]);

        // 3. Recent votes scoped via reports.tenant_id
        $voteRows = DB::table('votes')
            ->join('reports', 'reports.id', '=', 'votes.report_id')
            ->where('reports.tenant_id', $tenantId)
            ->whereNull('reports.deleted_at')
            ->orderByDesc('votes.created_at')
            ->limit($limit)
            ->get([
                'votes.created_at as created_at',
                'votes.report_id as report_id',
                'reports.title as report_title',
            ]);

        // 4. Recent status transitions (use updated_at as proxy when no audit table)
        $statusRows = DB::table('reports')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->whereColumn('updated_at', '!=', 'created_at')
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get([
                'id as report_id',
                'title as report_title',
                'status',
                'updated_at as created_at',
            ]);

        $events = [];

        foreach ($agentRows as $row) {
            $events[] = [
                'type'         => 'agent_event',
                'icon'         => 'sparkles',
                'message'      => $this->buildAgentMessage((string) $row->action, (string) $row->report_title),
                'created_at'   => SupportCarbon::parse($row->created_at),
                'report_id'    => (string) $row->report_id,
                'report_title' => (string) $row->report_title,
            ];
        }

        foreach ($integrationRows as $row) {
            $platform = ExternalPlatform::tryFrom((string) $row->platform);
            $status   = IntegrationJobStatus::tryFrom((string) $row->status);
            $events[] = [
                'type'         => 'integration_sync',
                'icon'         => 'cloud',
                'message'      => $this->buildIntegrationMessage($platform, $status, (string) $row->report_title),
                'created_at'   => SupportCarbon::parse($row->created_at),
                'report_id'    => (string) $row->report_id,
                'report_title' => (string) $row->report_title,
            ];
        }

        foreach ($voteRows as $row) {
            $events[] = [
                'type'         => 'vote_received',
                'icon'         => 'thumbs-up',
                'message'      => sprintf('Novo voto em «%s»', (string) $row->report_title),
                'created_at'   => SupportCarbon::parse($row->created_at),
                'report_id'    => (string) $row->report_id,
                'report_title' => (string) $row->report_title,
            ];
        }

        foreach ($statusRows as $row) {
            $status = ReportStatus::tryFrom((string) $row->status);
            $events[] = [
                'type'         => 'status_change',
                'icon'         => 'arrow-right-circle',
                'message'      => sprintf(
                    'Ticket «%s» mudou para %s',
                    (string) $row->report_title,
                    $status?->label() ?? (string) $row->status,
                ),
                'created_at'   => SupportCarbon::parse($row->created_at),
                'report_id'    => (string) $row->report_id,
                'report_title' => (string) $row->report_title,
            ];
        }

        usort(
            $events,
            static fn (array $a, array $b): int => $b['created_at']->getTimestamp() <=> $a['created_at']->getTimestamp(),
        );

        return array_slice($events, 0, $limit);
    }

    /**
     * @return array<int, array{id: string, title: string, status: ReportStatus, vote_count: int, voted_by_me: bool, product_name: string|null}>
     */
    public function topVotedImprovements(string $tenantId, ?string $userId = null, int $limit = 5): array
    {
        // Query builder bypasses Eloquent casts so status/type stay as strings.
        $rows = DB::table('reports')
            ->leftJoin('products', 'products.id', '=', 'reports.product_id')
            ->where('reports.tenant_id', $tenantId)
            ->whereNull('reports.deleted_at')
            ->whereIn('reports.type', [ReportType::Improvement->value, ReportType::FeatureRequest->value])
            ->where('reports.status', ReportStatus::PublishedForVoting->value)
            ->orderByDesc('reports.vote_count')
            ->limit($limit)
            ->get([
                'reports.id as id',
                'reports.title as title',
                'reports.status as status',
                'reports.vote_count as vote_count',
                'products.name as product_name',
            ]);

        $votedIds = [];
        if ($userId !== null && $rows->isNotEmpty()) {
            $votedIds = Vote::query()
                ->where('user_id', $userId)
                ->whereIn('report_id', $rows->pluck('id')->all())
                ->pluck('report_id')
                ->all();
        }

        return $rows
            ->map(static function (object $row) use ($votedIds): array {
                return [
                    'id'           => (string) $row->id,
                    'title'        => (string) $row->title,
                    'status'       => ReportStatus::from((string) $row->status),
                    'vote_count'   => (int) $row->vote_count,
                    'voted_by_me'  => in_array((string) $row->id, $votedIds, true),
                    'product_name' => $row->product_name !== null ? (string) $row->product_name : null,
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array{id: string, title: string, status: ReportStatus, type: ReportType, created_at: CarbonInterface}>
     */
    public function myTickets(string $tenantId, string $userId, int $limit = 5): array
    {
        return Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('author_id', $userId)
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get(['id', 'title', 'status', 'type', 'created_at'])
            ->map(static fn ($report): array => [
                'id'         => (string) $report->id,
                'title'      => (string) $report->title,
                'status'     => $report->status,
                'type'       => $report->type,
                'created_at' => $report->created_at,
            ])
            ->all();
    }

    /**
     * Returns reports grouped by product. Only returned when the tenant
     * owns more than one product; otherwise an empty array.
     *
     * @return array<int, array{product_id: string, product_name: string, open: int, in_progress: int, done: int}>
     */
    public function reportsByProduct(string $tenantId): array
    {
        return Cache::remember(
            $this->cacheKey($tenantId, 'reports_by_product'),
            self::CACHE_TTL_SECONDS,
            function () use ($tenantId): array {
                // Products are global; the tenant's "own" products are those joined via tenant_product.
                $productCount = DB::table('tenant_product')
                    ->where('tenant_id', $tenantId)
                    ->count();

                if ($productCount < 2) {
                    return [];
                }

                $rows = DB::table('products')
                    ->join('tenant_product', 'tenant_product.product_id', '=', 'products.id')
                    ->leftJoin('reports', function ($join) use ($tenantId): void {
                        $join->on('reports.product_id', '=', 'products.id')
                            ->where('reports.tenant_id', '=', $tenantId)
                            ->whereNull('reports.deleted_at');
                    })
                    ->where('tenant_product.tenant_id', $tenantId)
                    ->groupBy('products.id', 'products.name')
                    ->orderBy('products.name')
                    ->get([
                        'products.id as product_id',
                        'products.name as product_name',
                        DB::raw("COUNT(CASE WHEN reports.status NOT IN ('rejected', 'done') AND reports.status IS NOT NULL THEN 1 END) as open_count"),
                        DB::raw("COUNT(CASE WHEN reports.status = 'in_progress' THEN 1 END) as in_progress_count"),
                        DB::raw("COUNT(CASE WHEN reports.status = 'done' THEN 1 END) as done_count"),
                    ]);

                return $rows
                    ->map(static fn (object $row): array => [
                        'product_id'   => (string) $row->product_id,
                        'product_name' => (string) $row->product_name,
                        'open'         => (int) $row->open_count,
                        'in_progress'  => (int) $row->in_progress_count,
                        'done'         => (int) $row->done_count,
                    ])
                    ->all();
            },
        );
    }

    /**
     * @return array<int, array{platform: ExternalPlatform, is_active: bool, last_job_at: CarbonInterface|null, last_job_status: IntegrationJobStatus|null, failed_last_24h: int}>
     */
    public function integrationsHealth(string $tenantId): array
    {
        return Cache::remember(
            $this->cacheKey($tenantId, 'integrations_health'),
            self::CACHE_TTL_SECONDS,
            function () use ($tenantId): array {
                // Integrations are configured per product by root.
                // For a given tenant, surface the integrations of all products
                // assigned to that tenant via the tenant_product pivot.
                $tenant = Tenant::query()
                    ->whereKey($tenantId)
                    ->with(['products:id'])
                    ->first();

                if ($tenant === null) {
                    return [];
                }

                $productIds = $tenant->products->pluck('id')->all();

                if ($productIds === []) {
                    return [];
                }

                $integrations = ProductIntegration::query()
                    ->whereIn('product_id', $productIds)
                    ->get(['platform', 'is_active']);

                if ($integrations->isEmpty()) {
                    return [];
                }

                $platforms = $integrations->pluck('platform')
                    ->map(static fn (ExternalPlatform $p): string => $p->value)
                    ->unique()
                    ->all();

                // Latest job per platform (one query, scoped via reports)
                $latestJobs = DB::table('integration_jobs')
                    ->join('reports', 'reports.id', '=', 'integration_jobs.report_id')
                    ->where('reports.tenant_id', $tenantId)
                    ->whereIn('integration_jobs.platform', $platforms)
                    ->orderByDesc('integration_jobs.created_at')
                    ->get([
                        'integration_jobs.platform as platform',
                        'integration_jobs.status as status',
                        'integration_jobs.created_at as created_at',
                    ])
                    ->groupBy('platform')
                    ->map(static fn ($items) => $items->first());

                // Failures in last 24h per platform
                $failuresByPlatform = DB::table('integration_jobs')
                    ->join('reports', 'reports.id', '=', 'integration_jobs.report_id')
                    ->where('reports.tenant_id', $tenantId)
                    ->whereIn('integration_jobs.platform', $platforms)
                    ->where('integration_jobs.status', IntegrationJobStatus::Failed->value)
                    ->where('integration_jobs.created_at', '>=', now()->subDay())
                    ->groupBy('integration_jobs.platform')
                    ->select('integration_jobs.platform', DB::raw('COUNT(*) as failure_count'))
                    ->pluck('failure_count', 'integration_jobs.platform');

                return $integrations
                    ->unique(fn (ProductIntegration $integration): string => $integration->platform->value)
                    ->map(function (ProductIntegration $integration) use ($latestJobs, $failuresByPlatform): array {
                        $platformValue = $integration->platform->value;
                        $latest        = $latestJobs->get($platformValue);
                        $lastJobStatus = $latest !== null
                            ? IntegrationJobStatus::tryFrom((string) $latest->status)
                            : null;

                        return [
                            'platform'        => $integration->platform,
                            'is_active'       => (bool) $integration->is_active,
                            'last_job_at'     => $latest !== null ? SupportCarbon::parse($latest->created_at) : null,
                            'last_job_status' => $lastJobStatus,
                            'failed_last_24h' => (int) ($failuresByPlatform->get($platformValue) ?? 0),
                        ];
                    })
                    ->values()
                    ->all();
            },
        );
    }

    /**
     * @return array{open_tickets: int, resolved_this_month: int, avg_resolution_hours: float|null, active_votes: int, open_tickets_delta: int, resolved_delta: int, votes_delta: int}
     */
    private function computeHeaderCards(string $tenantId): array
    {
        $now             = now();
        $startOfMonth    = $now->copy()->startOfMonth();
        $weekAgo         = $now->copy()->subWeek();
        $twoWeeksAgo     = $now->copy()->subWeeks(2);
        $startPrevMonth  = $now->copy()->subMonthNoOverflow()->startOfMonth();
        $endPrevMonth    = $now->copy()->subMonthNoOverflow()->endOfMonth();

        // Open tickets: NOT IN (rejected, done)
        $openTickets = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->whereNotIn('status', [ReportStatus::Rejected->value, ReportStatus::Done->value])
            ->count();

        // Open tickets a week ago — anything created before weekAgo and not yet rejected/done by then
        $openTicketsLastWeek = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('created_at', '<', $weekAgo)
            ->whereNotIn('status', [ReportStatus::Rejected->value, ReportStatus::Done->value])
            ->count();

        // Resolved this month: status=done AND updated_at within current month
        $resolvedThisMonth = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('status', ReportStatus::Done->value)
            ->where('updated_at', '>=', $startOfMonth)
            ->count();

        $resolvedPrevMonth = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('status', ReportStatus::Done->value)
            ->whereBetween('updated_at', [$startPrevMonth, $endPrevMonth])
            ->count();

        // Avg resolution hours (last 90 days, status=done)
        $avgRow = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $tenantId)
            ->where('status', ReportStatus::Done->value)
            ->where('updated_at', '>=', $now->copy()->subDays(90))
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (updated_at - created_at)) / 3600) as avg_hours')
            ->first();

        $avgResolutionHours = $avgRow?->avg_hours !== null ? (float) $avgRow->avg_hours : null;

        // Active votes: votes received this week on PublishedForVoting reports of this tenant
        $activeVotes = DB::table('votes')
            ->join('reports', 'reports.id', '=', 'votes.report_id')
            ->where('reports.tenant_id', $tenantId)
            ->where('reports.status', ReportStatus::PublishedForVoting->value)
            ->whereNull('reports.deleted_at')
            ->where('votes.created_at', '>=', $weekAgo)
            ->count();

        $votesPrevWeek = DB::table('votes')
            ->join('reports', 'reports.id', '=', 'votes.report_id')
            ->where('reports.tenant_id', $tenantId)
            ->where('reports.status', ReportStatus::PublishedForVoting->value)
            ->whereNull('reports.deleted_at')
            ->whereBetween('votes.created_at', [$twoWeeksAgo, $weekAgo])
            ->count();

        return [
            'open_tickets'         => $openTickets,
            'resolved_this_month'  => $resolvedThisMonth,
            'avg_resolution_hours' => $avgResolutionHours,
            'active_votes'         => $activeVotes,
            'open_tickets_delta'   => $openTickets - $openTicketsLastWeek,
            'resolved_delta'       => $resolvedThisMonth - $resolvedPrevMonth,
            'votes_delta'          => $activeVotes - $votesPrevWeek,
        ];
    }

    /**
     * @return array{bug: array<string, int>, improvement: array<string, int>}
     */
    private function computeStatusFunnel(string $tenantId): array
    {
        // Bypass Eloquent casts (which would convert raw type/status into enums)
        // by querying through the query builder directly.
        $rows = DB::table('reports')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->whereIn('type', [ReportType::Bug->value, ReportType::Improvement->value, ReportType::FeatureRequest->value])
            ->groupBy('type', 'status')
            ->select('type', 'status', DB::raw('COUNT(*) as total'))
            ->get();

        $funnel = [
            'bug'         => $this->emptyStatusBuckets(),
            'improvement' => $this->emptyStatusBuckets(),
        ];

        foreach ($rows as $row) {
            // FeatureRequest is grouped under "improvement" for the funnel UI
            $bucket = ((string) $row->type) === ReportType::Bug->value ? 'bug' : 'improvement';
            $status = (string) $row->status;
            if (! array_key_exists($status, $funnel[$bucket])) {
                continue;
            }
            $funnel[$bucket][$status] += (int) $row->total;
        }

        return $funnel;
    }

    /**
     * @return array<string, int>
     */
    private function emptyStatusBuckets(): array
    {
        $buckets = [];
        foreach (ReportStatus::cases() as $status) {
            $buckets[$status->value] = 0;
        }
        return $buckets;
    }

    private function buildAgentMessage(string $action, string $reportTitle): string
    {
        $messages = [
            'enrichment_started'   => 'Enriquecimento iniciado em «%s»',
            'enrichment_completed' => 'Enriquecimento concluído em «%s»',
            'duplicate_detected'   => 'Possível duplicata detectada em «%s»',
            'risk_analyzed'        => 'Análise de risco concluída em «%s»',
            'branch_created'       => 'Branch criada para «%s»',
            'merge_request_opened' => 'Merge request aberto para «%s»',
        ];

        $template = $messages[$action] ?? 'Atividade do agente em «%s»';
        return sprintf($template, $reportTitle);
    }

    private function buildIntegrationMessage(?ExternalPlatform $platform, ?IntegrationJobStatus $status, string $reportTitle): string
    {
        $platformLabel = match ($platform) {
            ExternalPlatform::Jira   => 'Jira',
            ExternalPlatform::GitHub => 'GitHub',
            ExternalPlatform::GitLab => 'GitLab',
            default                  => 'Integração',
        };

        $verb = match ($status) {
            IntegrationJobStatus::Done       => 'concluída',
            IntegrationJobStatus::Failed     => 'falhou',
            IntegrationJobStatus::Processing => 'em andamento',
            IntegrationJobStatus::Pending    => 'pendente',
            default                          => 'atualizada',
        };

        return sprintf('Sincronização %s %s para «%s»', $platformLabel, $verb, $reportTitle);
    }

    private function cacheKey(string $tenantId, string $section): string
    {
        return sprintf('dashboard:%s:%s', $tenantId, $section);
    }
}
