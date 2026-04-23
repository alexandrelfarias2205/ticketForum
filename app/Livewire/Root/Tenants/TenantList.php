<?php declare(strict_types=1);

namespace App\Livewire\Root\Tenants;

use App\Actions\Tenants\DeactivateTenantAction;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class TenantList extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    public int $perPage = 15;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function tenants(): LengthAwarePaginator
    {
        return Tenant::query()
            ->when(
                $this->search,
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                             ->orWhere('slug', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate($this->perPage);
    }

    public function deactivate(string $id): void
    {
        $tenant = Tenant::findOrFail($id);

        $this->authorize('delete', $tenant);

        app(DeactivateTenantAction::class)->handle($tenant);

        $this->dispatch('notify', message: 'Empresa desativada com sucesso.', type: 'success');

        unset($this->tenants);
    }

    public function render(): View
    {
        return view('livewire.root.tenants.tenant-list');
    }
}
