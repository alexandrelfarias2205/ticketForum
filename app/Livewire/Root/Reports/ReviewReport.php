<?php declare(strict_types=1);

namespace App\Livewire\Root\Reports;

use App\Actions\Reports\ApproveReportAction;
use App\Actions\Reports\PublishReportAction;
use App\Actions\Reports\RejectReportAction;
use App\Models\Label;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class ReviewReport extends Component
{
    public Report $report;

    public string $enrichedTitle = '';

    public string $enrichedDescription = '';

    public array $selectedLabels = [];

    public bool $showRejectModal = false;

    public string $rejectReason = '';

    public function mount(Report $report): void
    {
        $this->authorize('approve', $report);

        $this->report = $report->load(['tenant', 'author', 'labels', 'attachments']);

        $this->enrichedTitle = $report->enriched_title ?? $report->title;
        $this->enrichedDescription = $report->enriched_description ?? $report->description;
        $this->selectedLabels = $report->labels->pluck('id')->toArray();
    }

    #[Computed]
    public function availableLabels(): Collection
    {
        return Label::all();
    }

    public function approve(ApproveReportAction $action): void
    {
        $this->authorize('approve', $this->report);

        $this->validate([
            'enrichedTitle'       => 'required|string|max:500',
            'enrichedDescription' => 'required|string',
        ]);

        $action->handle($this->report, auth()->user(), [
            'enriched_title'       => $this->enrichedTitle,
            'enriched_description' => $this->enrichedDescription,
            'label_ids'            => $this->selectedLabels,
        ]);

        $this->dispatch('notify', type: 'success', message: 'Relatório aprovado com sucesso!');

        $this->redirect(route('root.reports.index'), navigate: true);
    }

    public function reject(): void
    {
        $this->showRejectModal = true;
    }

    public function confirmReject(RejectReportAction $action): void
    {
        $this->authorize('approve', $this->report);

        $this->validate([
            'rejectReason' => 'required|string|min:5',
        ]);

        $action->handle($this->report, auth()->user(), $this->rejectReason);

        $this->dispatch('notify', type: 'info', message: 'Relatório rejeitado.');

        $this->redirect(route('root.reports.index'), navigate: true);
    }

    public function publish(PublishReportAction $action): void
    {
        $this->authorize('publish', $this->report);

        $action->handle($this->report);

        $this->dispatch('notify', type: 'success', message: 'Relatório publicado para votação!');

        $this->report->refresh();
        $this->report->load(['tenant', 'author', 'labels', 'attachments']);
    }

    public function render(): View
    {
        return view('livewire.root.reports.review-report');
    }
}
