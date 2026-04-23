<?php declare(strict_types=1);

namespace App\Livewire\Root\Delivered;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class DeliveredList extends Component
{
    use WithPagination;

    public string $filterTenant = '';
    public string $search = '';
    public string $filterPlatform = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Report::class);
    }

    #[Computed]
    public function reports()
    {
        return Report::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'labels', 'author'])
            ->where('status', ReportStatus::Done)
            ->when($this->filterTenant, fn($q) => $q->where('tenant_id', $this->filterTenant))
            ->when($this->filterPlatform, fn($q) => $q->where('external_platform', $this->filterPlatform))
            ->when($this->search, fn($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->latest('updated_at')
            ->paginate(20);
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

    public function updatedFilterTenant(): void
    {
        $this->resetPage();
    }

    public function updatedFilterPlatform(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.root.delivered.delivered-list');
    }
}
