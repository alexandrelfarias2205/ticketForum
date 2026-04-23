<?php declare(strict_types=1);

namespace App\Livewire\Root\Tenants;

use App\Actions\Tenants\UpdateTenantAction;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class EditTenant extends Component
{
    public Tenant $tenant;

    public string $name = '';
    public string $plan = 'free';
    public bool $is_active = true;

    public function mount(Tenant $tenant): void
    {
        $this->authorize('update', $tenant);

        $this->tenant    = $tenant;
        $this->name      = $tenant->name;
        $this->plan      = $tenant->plan->value;
        $this->is_active = $tenant->is_active;
    }

    protected function rules(): array
    {
        return [
            'name'      => ['required', 'string', 'max:255'],
            'plan'      => ['required', 'string', 'in:free,pro,enterprise'],
            'is_active' => ['boolean'],
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
        $this->authorize('update', $this->tenant);

        $this->validate();

        app(UpdateTenantAction::class)->handle($this->tenant, [
            'name'      => $this->name,
            'plan'      => $this->plan,
            'is_active' => $this->is_active,
        ]);

        $this->dispatch('notify', message: 'Empresa atualizada com sucesso.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.root.tenants.edit-tenant');
    }
}
