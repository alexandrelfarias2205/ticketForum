<?php declare(strict_types=1);

namespace App\Livewire\Root\Tenants;

use App\Actions\Tenants\CreateTenantAction;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CreateTenant extends Component
{
    public string $name = '';
    public string $plan = 'free';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'plan' => ['required', 'string', 'in:free,pro,enterprise'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório.',
            'name.max'      => 'O nome não pode ter mais de 255 caracteres.',
            'plan.required' => 'O plano é obrigatório.',
            'plan.in'       => 'Plano inválido.',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', Tenant::class);

        $this->validate();

        app(CreateTenantAction::class)->handle([
            'name' => $this->name,
            'plan' => $this->plan,
        ]);

        $this->dispatch('notify', message: 'Empresa criada com sucesso.', type: 'success');

        $this->redirect(route('root.tenants.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.root.tenants.create-tenant');
    }
}
