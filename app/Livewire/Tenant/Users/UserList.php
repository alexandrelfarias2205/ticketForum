<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Users;

use App\Actions\Users\DeactivateUserAction;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class UserList extends Component
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
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when(
                $this->search,
                fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                             ->orWhere('email', 'like', "%{$this->search}%")
            )
            ->latest()
            ->paginate($this->perPage);
    }

    public function deactivate(string $id): void
    {
        $user = User::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $this->authorize('delete', $user);

        app(DeactivateUserAction::class)->handle($user);

        $this->dispatch('notify', message: 'Usuário desativado com sucesso.', type: 'success');

        unset($this->users);
    }

    public function render(): View
    {
        return view('livewire.tenant.users.user-list');
    }
}
