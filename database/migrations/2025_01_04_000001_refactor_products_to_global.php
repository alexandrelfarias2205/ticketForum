<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products are global — owned by the platform, not tenants.
        Schema::table('products', function (Blueprint $table): void {
            // Drop the unique (tenant_id, slug) index before removing the column.
            $table->dropUnique(['tenant_id', 'slug']);
            $table->dropIndex(['tenant_id']);
            $table->dropIndex(['tenant_id', 'is_active']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
        });

        // Re-add a global unique slug constraint (was previously per-tenant).
        Schema::table('products', function (Blueprint $table): void {
            $table->unique('slug');
            $table->index('is_active');
        });

        // Pivot table linking tenants to the global products they may report on.
        Schema::create('tenant_product', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('product_id');
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');

            $table->unique(['tenant_id', 'product_id'], 'uq_tenant_product');
            $table->index('tenant_id', 'idx_tenant_product_tenant');
            $table->index('product_id', 'idx_tenant_product_product');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_product');

        Schema::table('products', function (Blueprint $table): void {
            $table->dropUnique(['slug']);
            $table->dropIndex(['is_active']);
        });

        Schema::table('products', function (Blueprint $table): void {
            $table->uuid('tenant_id')->nullable();
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->unique(['tenant_id', 'slug']);
            $table->index('tenant_id');
            $table->index(['tenant_id', 'is_active']);
        });
    }
};
