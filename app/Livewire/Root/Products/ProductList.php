<?php declare(strict_types=1);

namespace App\Livewire\Root\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\Computed;
use Livewire\Component;

final class ProductList extends Component
{
    public function mount(): void
    {
        $this->authorize('viewAny', Product::class);
    }

    #[Computed]
    public function products(): Collection
    {
        return Product::query()
            ->withCount(['reports', 'tenants'])
            ->orderBy('name')
            ->get();
    }

    public function toggleActive(string $id): void
    {
        $product = Product::findOrFail($id);

        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);

        unset($this->products);

        $status = $product->is_active ? 'ativado' : 'arquivado';
        $this->dispatch('notify', type: 'success', message: "Produto {$status} com sucesso.");
    }

    public function delete(string $id): void
    {
        $product = Product::withCount('reports')->findOrFail($id);

        $this->authorize('delete', $product);

        if ($product->reports_count > 0) {
            $this->dispatch('notify', type: 'error', message: 'Não é possível excluir um produto com relatórios vinculados.');
            return;
        }

        $product->delete();

        unset($this->products);

        $this->dispatch('notify', type: 'success', message: 'Produto excluído com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.root.products.product-list')->layout('components.layouts.root');
    }
}
