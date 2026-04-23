<?php declare(strict_types=1);

namespace App\Livewire\Root\Voting;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class VotingRanking extends Component
{
    use WithPagination;

    public string $filterType = '';
    public string $filterTenant = '';
    public string $search = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Report::class);
    }

    #[Computed]
    public function reports()
    {
        return Report::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'labels'])
            ->whereIn('status', [ReportStatus::PublishedForVoting, ReportStatus::InProgress, ReportStatus::Done])
            ->withExists(['votes as voted_by_me' => fn($q) => $q->where('user_id', auth()->id())])
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterTenant, fn($q) => $q->where('tenant_id', $this->filterTenant))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->orderByDesc('vote_count')
            ->paginate(30);
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::orderBy('name')->get(['id', 'name']);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedFilterType(): void
    {
        $this->resetPage();
    }

    public function updatedFilterTenant(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.root.voting.voting-ranking');
    }
}
