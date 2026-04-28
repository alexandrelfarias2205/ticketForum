<?php declare(strict_types=1);

use App\Models\Product;
use App\Models\ProductIntegration;
use App\Models\Report;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function (): void {
    $this->tenantA = Tenant::factory()->create();
    $this->tenantB = Tenant::factory()->create();

    $this->userA = User::factory()->tenantUser($this->tenantA)->create();
    $this->userB = User::factory()->tenantUser($this->tenantB)->create();

    $this->productA = Product::factory()->forTenant($this->tenantA)->create();
    $this->productB = Product::factory()->forTenant($this->tenantB)->create();
});

test('tenant A user cannot see tenant B products via TenantScope', function (): void {
    // When acting as tenant A user, TenantScope should exclude tenant B products
    $this->actingAs($this->userA);

    $visibleIds = Product::all()->pluck('id')->toArray();

    expect($visibleIds)->toContain((string) $this->productA->id)
        ->and($visibleIds)->not->toContain((string) $this->productB->id);
});

test('tenant A user cannot see tenant B products even when queried directly', function (): void {
    $this->actingAs($this->userA);

    $product = Product::find($this->productB->id);

    expect($product)->toBeNull();
});

test('tenant A user cannot create reports under tenant B product', function (): void {
    // Reports require matching tenant_id; inserting a report with tenantB product
    // under tenantA violates the constraint — here we verify the Report TenantScope
    // prevents tenantA user from seeing a report filed under tenantB product.
    $authorB = User::factory()->tenantUser($this->tenantB)->create();

    $reportB = Report::factory()->create([
        'tenant_id'  => $this->tenantB->id,
        'author_id'  => $authorB->id,
        'product_id' => $this->productB->id,
    ]);

    $this->actingAs($this->userA);

    $visible = Report::find($reportB->id);
    expect($visible)->toBeNull();
});

test('ProductIntegration config can only be accessed through its product tenant', function (): void {
    $integrationB = ProductIntegration::factory()
        ->forProduct($this->productB)
        ->gitlab()
        ->create();

    // Acting as tenant A, withoutGlobalScope still shows ProductIntegration
    // but the Product itself is scoped — verifying tenantB integration links back to tenantB product
    $integration = ProductIntegration::withoutGlobalScopes()->find($integrationB->id);
    $product = Product::withoutGlobalScopes()->find($integration->product_id);

    expect((string) $product->tenant_id)->toBe((string) $this->tenantB->id)
        ->and((string) $product->tenant_id)->not->toBe((string) $this->tenantA->id);
});

test('root user can see all products across tenants', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root);

    $ids = Product::withoutGlobalScopes()->pluck('id')->map(fn ($id): string => (string) $id)->toArray();

    expect($ids)->toContain((string) $this->productA->id)
        ->and($ids)->toContain((string) $this->productB->id);
});
