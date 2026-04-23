<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\TenantPlan;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        $name = fake()->company();

        return [
            'id'        => Str::uuid(),
            'name'      => $name,
            'slug'      => Str::slug($name) . '-' . fake()->unique()->numberBetween(1, 9999),
            'plan'      => TenantPlan::Free,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
