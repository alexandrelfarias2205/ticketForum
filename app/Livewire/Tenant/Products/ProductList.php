<?php declare(strict_types=1);

namespace App\Livewire\Tenant\Products;

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
            ->where('tenant_id', auth()->user()->tenant_id)
            ->withCount('reports')
            ->orderBy('name')
            ->get();
    }

    public function toggleActive(string $id): void
    {
        $this->authorize('viewAny', Product::class);

        $product = Product::where('tenant_id', auth()->user()->tenant_id)->findOrFail($id);

        $this->authorize('update', $product);

        $product->update(['is_active' => ! $product->is_active]);

        unset($this->products);

        $status = $product->is_active ? 'ativado' : 'desativado';
        session()->flash('success', "Produto {$status} com sucesso.");
    }

    public function delete(string $id): void
    {
        $this->authorize('viewAny', Product::class);

        $product = Product::where('tenant_id', auth()->user()->tenant_id)
            ->withCount('reports')
            ->findOrFail($id);

        $this->authorize('delete', $product);

        if ($product->reports_count > 0) {
            session()->flash('error', 'Não é possível excluir um produto com relatórios vinculados.');
            return;
        }

        $product->delete();

        unset($this->products);

        session()->flash('success', 'Produto excluído com sucesso.');
    }

    public function render(): View
    {
        return view('livewire.tenant.products.product-list');
    }
}
