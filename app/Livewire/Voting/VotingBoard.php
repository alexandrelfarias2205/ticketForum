<?php declare(strict_types=1);

namespace App\Livewire\Voting;

use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class VotingBoard extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $filterType = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'votes';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingFilterType(): void
    {
        $this->resetPage();
    }

    public function updatingSortBy(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function reports(): LengthAwarePaginator
    {
        return Report::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'labels', 'author'])
            ->where('status', ReportStatus::PublishedForVoting)
            ->withExists(['votes as voted_by_me' => fn ($q) => $q->where('user_id', auth()->id())])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->sortBy === 'votes', fn ($q) => $q->orderByDesc('vote_count'))
            ->when($this->sortBy === 'newest', fn ($q) => $q->orderByDesc('published_at'))
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.voting.voting-board');
    }
}
