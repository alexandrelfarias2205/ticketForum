<?php declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Reports\UpdateReportAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class EditReport extends Component
{
    public Report $report;

    #[Validate('required|in:bug,improvement,feature_request')]
    public string $type = '';

    #[Validate('required|string|max:500')]
    public string $title = '';

    #[Validate('required|string|min:10')]
    public string $description = '';

    public function mount(Report $report): void
    {
        $this->authorize('update', $report);

        abort_unless($report->status === ReportStatus::PendingReview, 403, 'Este relatório não pode mais ser editado.');

        $this->report = $report;
        $this->type = $report->type->value;
        $this->title = $report->title;
        $this->description = $report->description;
    }

    public function save(UpdateReportAction $action): void
    {
        $this->authorize('update', $this->report);

        abort_unless($this->report->status === ReportStatus::PendingReview, 403, 'Este relatório não pode mais ser editado.');

        $this->validate();

        $action->handle($this->report, $this->only(['type', 'title', 'description']));

        $this->dispatch('notify', type: 'success', message: 'Relatório atualizado com sucesso!');

        $this->redirect(route('app.reports.show', $this->report), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.reports.edit-report');
    }
}
