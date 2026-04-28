<?php declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'id'             => Str::uuid(),
            'name'           => ucfirst($name),
            'slug'           => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 99999),
            'description'    => fake()->sentence(),
            'repository_url' => null,
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
