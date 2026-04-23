<?php declare(strict_types=1);

namespace App\Livewire\Reports;

use App\Actions\Reports\CreateReportAction;
use App\Actions\Reports\StoreLinkAttachmentAction;
use App\Models\Report;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class CreateReport extends Component
{
    #[Validate('required|in:bug,improvement,feature_request')]
    public string $type = '';

    #[Validate('required|string|max:500')]
    public string $title = '';

    #[Validate('required|string|min:10')]
    public string $description = '';

    #[Validate(['links.*' => 'url|max:2048'])]
    public array $links = [];

    public string $newLink = '';

    public function addLink(): void
    {
        $this->validateOnly('newLink', ['newLink' => 'url|max:2048']);

        if (filled($this->newLink)) {
            $this->links[] = $this->newLink;
            $this->newLink = '';
        }
    }

    public function removeLink(int $index): void
    {
        unset($this->links[$index]);
        $this->links = array_values($this->links);
    }

    public function save(CreateReportAction $action, StoreLinkAttachmentAction $linkAction): void
    {
        $this->authorize('create', Report::class);

        $this->validate();

        $report = $action->handle(
            auth()->user(),
            $this->only(['type', 'title', 'description'])
        );

        foreach ($this->links as $url) {
            $linkAction->handle($report, $url);
        }

        $this->dispatch('notify', type: 'success', message: 'Relatório enviado com sucesso!');

        $this->redirect(route('app.reports.show', $report), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.reports.create-report');
    }
}
