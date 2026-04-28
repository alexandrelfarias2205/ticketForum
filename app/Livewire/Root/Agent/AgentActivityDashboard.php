<?php declare(strict_types=1);

namespace App\Livewire\Root\Agent;

use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

final class AgentActivityDashboard extends Component
{
    use WithPagination;

    public function mount(): void
    {
        abort_unless(auth()->user()->isRoot(), 403);
    }

    #[Computed]
    public function agentReports(): LengthAwarePaginator
    {
        return Report::withoutGlobalScopes()
            ->whereNotNull('agent_branch')
            ->with([
                'product',
                'agentLogs' => fn ($q) => $q->latest()->limit(3),
            ])
            ->withCount('agentLogs')
            ->orderByDesc('updated_at')
            ->paginate(20);
    }

    public function render(): View
    {
        return view('livewire.root.agent.agent-activity-dashboard');
    }
}
