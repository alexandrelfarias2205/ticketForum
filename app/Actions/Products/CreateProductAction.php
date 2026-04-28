<?php declare(strict_types=1);

namespace App\Actions\Products;

use App\Models\Product;
use App\Models\Tenant;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

final class CreateProductAction
{
    /**
     * Create a Product belonging to the given Tenant. The slug is unique within the tenant.
     *
     * @param  array{name: string, slug?: string|null, description?: string|null, is_active?: bool}  $data
     */
    public function handle(Tenant $tenant, array $data): Product
    {
        $name = trim((string) $data['name']);
        $slug = isset($data['slug']) && $data['slug'] !== null && $data['slug'] !== ''
            ? Str::slug((string) $data['slug'])
            : Str::slug($name);

        $exists = Product::withoutGlobalScopes()
            ->where('tenant_id', $tenant->id)
            ->where('slug', $slug)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'slug' => 'Já existe um produto com este identificador para o tenant.',
            ]);
        }

        return Product::create([
            'tenant_id'   => $tenant->id,
            'name'        => $name,
            'slug'        => $slug,
            'description' => $data['description'] ?? null,
            'is_active'   => $data['is_active'] ?? true,
        ]);
    }
}
