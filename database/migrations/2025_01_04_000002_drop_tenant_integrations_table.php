<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Integrations are now configured per product by root, not per tenant.
     * The product_integrations table already exists (created in 2025_01_02_000002).
     * We simply retire the legacy tenant_integrations table.
     */
    public function up(): void
    {
        Schema::dropIfExists('tenant_integrations');
    }

    public function down(): void
    {
        Schema::create('tenant_integrations', function (\Illuminate\Database\Schema\Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id')->unique();
            $table->string('platform');
            $table->text('config');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index('tenant_id');
        });
    }
};
