<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Dashboard;

use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Vote;
use App\Repositories\DashboardRepository;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class TenantDashboard extends Component
{
    /** Sets to true once the deferred (lazy) sections should populate. */
    public bool $loadDeferred = false;

    /**
     * Trigger the heavy/lazy sections — top voted, by product, integrations.
     * Wired in the view via `wire:init`.
     */
    public function loadDeferredSections(): void
    {
        $this->loadDeferred = true;
    }

    /**
     * Filter handler for status funnel — redirects to the reports list
     * with the chosen status as a query string.
     */
    public function filterByStatus(string $status): mixed
    {
        return redirect()->route('app.reports.index', ['status' => $status]);
    }

    /**
     * Cast a vote on the given report from inside the dashboard.
     * Mirrors the vote behaviour of \App\Livewire\Voting\VotingBoard.
     */
    public function vote(string $reportId): void
    {
        $report = Report::query()
            ->withoutGlobalScope(TenantScope::class)
            ->where('tenant_id', $this->tenantId())
            ->where('id', $reportId)
            ->firstOrFail();

        try {
            $this->authorize('create', [Vote::class, $report]);
        } catch (AuthorizationException) {
            $this->dispatch('notify', type: 'error', message: 'Você já votou nesta melhoria');
            return;
        }

        try {
            Vote::create([
                'user_id'   => auth()->id(),
                'report_id' => $report->id,
            ]);

            Report::query()
                ->withoutGlobalScope(TenantScope::class)
                ->where('id', $report->id)
                ->increment('vote_count');

            unset($this->topVoted);
            $this->dispatch('notify', type: 'success', message: 'Voto registrado');
        } catch (QueryException) {
            $this->dispatch('notify', type: 'error', message: 'Você já votou nesta melhoria');
        }
    }

    #[Computed]
    public function headerCards(): array
    {
        return $this->repository()->headerCards($this->tenantId());
    }

    #[Computed]
    public function funnel(): array
    {
        return $this->repository()->statusFunnel($this->tenantId());
    }

    #[Computed]
    public function feed(): array
    {
        return $this->repository()->activityFeed($this->tenantId(), 10);
    }

    #[Computed]
    public function topVoted(): array
    {
        if (! $this->loadDeferred) {
            return [];
        }

        return $this->repository()->topVotedImprovements(
            $this->tenantId(),
            (string) auth()->id(),
            5,
        );
    }

    #[Computed]
    public function myTickets(): array
    {
        if (! $this->canSeeMyTickets()) {
            return [];
        }

        return $this->repository()->myTickets(
            $this->tenantId(),
            (string) auth()->id(),
            5,
        );
    }

    #[Computed]
    public function byProduct(): array
    {
        if (! $this->loadDeferred) {
            return [];
        }

        return $this->repository()->reportsByProduct($this->tenantId());
    }

    #[Computed]
    public function integrations(): array
    {
        if (! $this->loadDeferred || ! $this->canSeeIntegrations()) {
            return [];
        }

        return $this->repository()->integrationsHealth($this->tenantId());
    }

    #[Computed]
    public function canSeeIntegrations(): bool
    {
        return auth()->check() && auth()->user()->isTenantAdmin();
    }

    #[Computed]
    public function canSeeMyTickets(): bool
    {
        return auth()->check() && auth()->user()->isTenantUser();
    }

    public function render(): View
    {
        return view('livewire.tenant.dashboard.tenant-dashboard');
    }

    private function repository(): DashboardRepository
    {
        return app(DashboardRepository::class);
    }

    private function tenantId(): string
    {
        $id = auth()->user()?->tenant_id;
        if (! $id) {
            abort(403, 'Tenant not found.');
        }
        return (string) $id;
    }
}
