<?php declare(strict_types=1);

use App\Enums\ExternalPlatform;
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

    // Products are global — both tenants exist in the catalogue, but each is only
    // attached (via tenant_product pivot) to its own tenant.
    $this->productA = Product::factory()->create();
    $this->productB = Product::factory()->create();

    $this->tenantA->products()->attach($this->productA);
    $this->tenantB->products()->attach($this->productB);
});

test('tenant A only sees its assigned products via the tenant pivot', function (): void {
    $assigned = $this->tenantA->products()->pluck('products.id')->all();

    expect($assigned)->toContain((string) $this->productA->id)
        ->and($assigned)->not->toContain((string) $this->productB->id);
});

test('Reports remain isolated by tenant_id even when product_id is shared', function (): void {
    // A product COULD be shared by both tenants — assignment is independent of report visibility
    $this->tenantA->products()->attach($this->productB);

    $authorB = User::factory()->tenantUser($this->tenantB)->create();

    $reportB = Report::factory()->create([
        'tenant_id'  => $this->tenantB->id,
        'author_id'  => $authorB->id,
        'product_id' => $this->productB->id,
    ]);

    $this->actingAs($this->userA);

    // Even though tenantA now has access to productB, reports remain tenant-scoped
    $visible = Report::find($reportB->id);
    expect($visible)->toBeNull();
});

test('ProductIntegration is owned by the global product, not by any single tenant', function (): void {
    $integrationB = ProductIntegration::factory()
        ->forProduct($this->productB)
        ->gitlab()
        ->create();

    // Integrations are stored against the global product. Multiple tenants attached
    // to the same product would share its integration — this is the intended design.
    $integration = ProductIntegration::find($integrationB->id);
    $product     = Product::find($integration->product_id);

    expect((string) $product->id)->toBe((string) $this->productB->id);
});

test('root user can see all products across tenants', function (): void {
    $root = User::factory()->root()->create();

    $this->actingAs($root);

    // Products are global — no scope to bypass.
    $ids = Product::pluck('id')->map(fn ($id): string => (string) $id)->toArray();

    expect($ids)->toContain((string) $this->productA->id)
        ->and($ids)->toContain((string) $this->productB->id);
});
