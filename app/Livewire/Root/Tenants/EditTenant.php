<?php declare(strict_types=1);

namespace App\Livewire\Root\Tenants;

use App\Actions\Tenants\UpdateTenantAction;
use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

class EditTenant extends Component
{
    public Tenant $tenant;

    public string $name = '';
    public string $plan = 'free';
    public bool $is_active = true;

    /** @var array<int, string> */
    public array $selectedProducts = [];

    public function mount(Tenant $tenant): void
    {
        $this->authorize('update', $tenant);

        $this->tenant    = $tenant;
        $this->name      = $tenant->name;
        $this->plan      = $tenant->plan->value;
        $this->is_active = $tenant->is_active;

        $this->selectedProducts = $tenant->products()
            ->pluck('products.id')
            ->map(fn ($id): string => (string) $id)
            ->all();
    }

    #[Computed]
    public function allProducts(): Collection
    {
        return Product::query()
            ->orderBy('name')
            ->get(['id', 'name', 'is_active']);
    }

    protected function rules(): array
    {
        return [
            'name'                => ['required', 'string', 'max:255'],
            'plan'                => ['required', 'string', 'in:free,pro,enterprise'],
            'is_active'           => ['boolean'],
            'selectedProducts'    => ['array'],
            'selectedProducts.*'  => ['string', 'uuid'],
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

        $this->tenant->products()->sync($this->selectedProducts);

        $this->dispatch('notify', message: 'Empresa atualizada com sucesso.', type: 'success');
    }

    public function render(): View
    {
        return view('livewire.root.tenants.edit-tenant');
    }
}
