<?php declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Reports\DeleteAttachmentAction;
use App\Actions\Reports\StoreLinkAttachmentAction;
use App\Enums\ReportStatus;
use App\Models\Report;
use App\Models\ReportAttachment;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ShowReport extends Component
{
    public Report $report;

    public bool $showUploadForm = false;

    public string $newLink = '';

    public function mount(Report $report): void
    {
        $this->authorize('view', $report);
        $this->report = $report->load(['author', 'labels', 'attachments', 'tenant']);
    }

    public function addLink(StoreLinkAttachmentAction $action): void
    {
        $this->authorize('update', $this->report);

        $this->validateOnly('newLink', ['newLink' => 'required|url|max:2048']);

        $action->handle($this->report, $this->newLink);

        $this->newLink = '';
        $this->report->load('attachments');

        $this->dispatch('notify', type: 'success', message: 'Link adicionado com sucesso!');
    }

    public function deleteAttachment(string $attachmentId, DeleteAttachmentAction $action): void
    {
        $this->authorize('update', $this->report);

        $attachment = $this->report->attachments()->findOrFail($attachmentId);

        $action->handle($attachment);

        $this->report->load('attachments');

        $this->dispatch('notify', type: 'success', message: 'Anexo removido com sucesso!');
    }

    public function render(): View
    {
        return view('livewire.reports.show-report');
    }
}
