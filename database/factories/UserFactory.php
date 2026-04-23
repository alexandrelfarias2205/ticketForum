<?php declare(strict_types=1);

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{
    protected $model = User::class;

    protected static ?string $password = null;

    public function definition(): array
    {
        return [
            'id'                => Str::uuid(),
            'tenant_id'         => null,
            'name'              => fake()->name(),
            'email'             => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password'          => static::$password ??= Hash::make('password'),
            'role'              => UserRole::TenantUser,
            'is_active'         => true,
            'remember_token'    => Str::random(10),
        ];
    }

    public function root(): static
    {
        return $this->state(['role' => UserRole::Root, 'tenant_id' => null]);
    }

    public function tenantAdmin(Tenant $tenant): static
    {
        return $this->state(['role' => UserRole::TenantAdmin, 'tenant_id' => $tenant->id]);
    }

    public function tenantUser(Tenant $tenant): static
    {
        return $this->state(['role' => UserRole::TenantUser, 'tenant_id' => $tenant->id]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }

    public function unverified(): static
    {
        return $this->state(['email_verified_at' => null]);
    }
}
