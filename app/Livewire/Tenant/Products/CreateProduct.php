<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class CreateProduct extends Component
{
    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('nullable|url|max:255')]
    public string $repositoryUrl = '';

    public function mount(): void
    {
        $this->authorize('create', Product::class);
    }

    public function save(): void
    {
        $this->authorize('create', Product::class);

        $this->validate();

        Product::create([
            'tenant_id'   => auth()->user()->tenant_id,
            'name'        => $this->name,
            'description' => $this->description ?: null,
            'is_active'   => true,
        ]);

        session()->flash('success', 'Produto criado com sucesso.');

        $this->redirect(route('app.products.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenant.products.create-product');
    }
}
