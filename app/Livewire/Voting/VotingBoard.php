<?php declare(strict_types=1);

namespace App\Livewire\Voting;

use App\Enums\ReportStatus;
use App\Models\Product;
use App\Models\Report;
use App\Models\Scopes\TenantScope;
use App\Models\Vote;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\QueryException;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

final class VotingBoard extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url(as: 'type')]
    public string $filterType = '';

    #[Url(as: 'sort')]
    public string $sortBy = 'votes';

    #[Url(as: 'product')]
    public ?string $filterProductId = null;

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

    public function updatingFilterProductId(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::where('is_active', true)->orderBy('name')->get();
    }

    #[Computed]
    public function reports(): LengthAwarePaginator
    {
        return Report::withoutGlobalScope(TenantScope::class)
            ->with(['tenant', 'labels', 'author', 'product'])
            ->where('status', ReportStatus::PublishedForVoting)
            ->withExists(['votes as voted_by_me' => fn ($q) => $q->where('user_id', auth()->id())])
            ->when($this->search, fn ($q) => $q->where('title', 'like', "%{$this->search}%"))
            ->when($this->filterType, fn ($q) => $q->where('type', $this->filterType))
            ->when($this->filterProductId, fn ($q) => $q->where('product_id', $this->filterProductId))
            ->when($this->sortBy === 'votes', fn ($q) => $q->orderByDesc('vote_count'))
            ->when($this->sortBy === 'newest', fn ($q) => $q->orderByDesc('published_at'))
            ->paginate(20);
    }

    #[Computed]
    public function hasVoted(string $reportId): bool
    {
        return Vote::where('user_id', auth()->id())
            ->where('report_id', $reportId)
            ->exists();
    }

    public function vote(string $reportId): void
    {
        $this->authorize('create', Vote::class);

        try {
            Vote::create([
                'user_id'   => auth()->id(),
                'report_id' => $reportId,
            ]);

            Report::withoutGlobalScope(TenantScope::class)
                ->where('id', $reportId)
                ->increment('vote_count');

            unset($this->reports);
        } catch (QueryException $e) {
            $this->dispatch('notify', type: 'error', message: 'Você já votou nesta melhoria');
        }
    }

    public function render(): View
    {
        return view('livewire.voting.voting-board');
    }
}
