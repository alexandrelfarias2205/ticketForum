<?php declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ReportList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $filterType = '';

    #[Url(as: 'status')]
    public string $filterStatus = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingFilterStatus(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function reports(): LengthAwarePaginator
    {
        return Report::query()
            ->with(['author', 'labels', 'attachments'])
            ->when(
                $this->search,
                fn ($q) => $q->where('title', 'like', "%{$this->search}%")
                             ->orWhere('description', 'like', "%{$this->search}%")
            )
            ->when(
                $this->filterType,
                fn ($q) => $q->where('type', $this->filterType)
            )
            ->when(
                $this->filterStatus,
                fn ($q) => $q->where('status', $this->filterStatus)
            )
            ->latest()
            ->paginate($this->perPage);
    }

    public function render(): View
    {
        return view('livewire.reports.report-list');
    }
}
