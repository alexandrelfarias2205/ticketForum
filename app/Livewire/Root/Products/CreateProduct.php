<?php declare(strict_types=1);

namespace App\Livewire\Root\Products;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Livewire\Component;

final class CreateProduct extends Component
{
    public string $name = '';
    public string $description = '';
    public string $repository_url = '';
    public bool $is_active = true;

    public function mount(): void
    {
        $this->authorize('create', Product::class);
    }

    protected function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'description'    => ['nullable', 'string', 'max:5000'],
            'repository_url' => ['nullable', 'url', 'max:500'],
            'is_active'      => ['boolean'],
        ];
    }

    protected function messages(): array
    {
        return [
            'name.required'      => 'O nome é obrigatório.',
            'name.max'           => 'O nome não pode ter mais de 255 caracteres.',
            'description.max'    => 'A descrição é muito longa.',
            'repository_url.url' => 'A URL do repositório deve ser válida.',
            'repository_url.max' => 'A URL do repositório é muito longa.',
        ];
    }

    public function save(): void
    {
        $this->authorize('create', Product::class);

        $this->validate();

        Product::create([
            'name'           => $this->name,
            'slug'           => Str::slug($this->name) . '-' . Str::lower(Str::random(6)),
            'description'    => $this->description !== '' ? $this->description : null,
            'repository_url' => $this->repository_url !== '' ? $this->repository_url : null,
            'is_active'      => $this->is_active,
        ]);

        session()->flash('success', 'Produto criado com sucesso.');

        $this->redirect(route('root.products.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.root.products.create-product')->layout('components.layouts.root');
    }
}
