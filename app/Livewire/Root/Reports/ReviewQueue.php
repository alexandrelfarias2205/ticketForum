<?php declare(strict_types=1);

namespace App\Livewire\Root\Reports;

use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReviewQueue extends Component
{
    use WithPagination;

    #[Url(as: 'status')]
    public string $filterStatus = 'pending_review';

    #[Url(as: 'tenant')]
    public string $filterTenant = '';

    #[Url(as: 'q')]
    public string $search = '';

    public int $perPage = 20;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    public function updatingFilterTenant(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tenants()
    {
        return Tenant::query()->orderBy('name')->get(['id', 'name']);
    }

    #[Computed]
    public function reports(): LengthAwarePaginator
    {
        return Report::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'author', 'labels'])
            ->when(
                $this->filterStatus,
                fn ($q) => $q->where('status', $this->filterStatus)
            )
            ->when(
                $this->filterTenant,
                fn ($q) => $q->where('tenant_id', $this->filterTenant)
            )
            ->when(
                $this->search,
                fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                             ->orWhere('description', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.root.reports.review-queue');
    }
}
