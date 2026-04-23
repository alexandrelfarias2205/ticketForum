<?php declare(strict_types=1);

namespace App\Livewire\Root\Labels;

use App\Actions\Labels\CreateLabelAction;
use App\Actions\Labels\DeleteLabelAction;
use App\Actions\Labels\UpdateLabelAction;
use App\Models\Label;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;

class LabelManager extends Component
{
    public Collection $labels;
    public string $name = '';
    public string $color = '#6366f1';
    public string|null $editingId = null;
    public bool $showForm = false;

    public function mount(): void
    {
        $this->labels = Label::orderBy('name')->get();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Label::class);
        $this->resetForm();
        $this->showForm = true;
        $this->editingId = null;
    }

    public function openEdit(string $id): void
    {
        $label = Label::findOrFail($id);
        $this->authorize('update', $label);

        $this->name      = $label->name;
        $this->color     = $label->color;
        $this->editingId = $id;
        $this->showForm  = true;
    }

    public function save(CreateLabelAction $create, UpdateLabelAction $update): void
    {
        if ($this->editingId) {
            $this->authorize('update', Label::findOrFail($this->editingId));
        } else {
            $this->authorize('create', Label::class);
        }

        $uniqueRule = $this->editingId
            ? 'unique:labels,name,' . $this->editingId
            : 'unique:labels,name';

        $this->validate([
            'name'  => ['required', 'string', 'max:100', $uniqueRule],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
        ]);

        if ($this->editingId) {
            $label = Label::findOrFail($this->editingId);
            $update->handle($label, ['name' => $this->name, 'color' => $this->color]);
            $message = 'Etiqueta atualizada com sucesso.';
        } else {
            $create->handle(auth()->user(), ['name' => $this->name, 'color' => $this->color]);
            $message = 'Etiqueta criada com sucesso.';
        }

        $this->labels = Label::orderBy('name')->get();
        $this->resetForm();
        $this->dispatch('notify', message: $message, type: 'success');
    }

    public function delete(string $id, DeleteLabelAction $action): void
    {
        $label = Label::findOrFail($id);
        $this->authorize('delete', $label);

        $action->handle($label);

        $this->labels = Label::orderBy('name')->get();
        $this->dispatch('notify', message: 'Etiqueta excluída com sucesso.', type: 'success');
    }

    public function resetForm(): void
    {
        $this->name      = '';
        $this->color     = '#6366f1';
        $this->editingId = null;
        $this->showForm  = false;
    }

    public function render(): View
    {
        return view('livewire.root.labels.label-manager');
    }
}
