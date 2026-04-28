<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

final class EditProduct extends Component
{
    public Product $product;

    #[Validate('required|string|max:100')]
    public string $name = '';

    #[Validate('nullable|string|max:500')]
    public string $description = '';

    #[Validate('nullable|url|max:255')]
    public string $repositoryUrl = '';

    public function mount(Product $product): void
    {
        $this->authorize('update', $product);

        $this->product       = $product;
        $this->name          = $product->name;
        $this->description   = $product->description ?? '';
        $this->repositoryUrl = $product->repository_url ?? '';
    }

    public function save(): void
    {
        $this->authorize('update', $this->product);

        $this->validate();

        $this->product->update([
            'name'           => $this->name,
            'description'    => $this->description ?: null,
            'repository_url' => $this->repositoryUrl ?: null,
        ]);

        session()->flash('success', 'Produto atualizado com sucesso.');

        $this->redirect(route('app.products.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.tenant.products.edit-product');
    }
}
