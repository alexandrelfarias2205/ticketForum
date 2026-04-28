<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop the synthetic id PK from tenant_product. Laravel's BelongsToMany pivot
     * does not auto-populate UUIDs on attach(), so the canonical Laravel pivot
     * shape (composite PK on the two FKs) is what we need.
     */
    public function up(): void
    {
        Schema::table('tenant_product', function (Blueprint $table): void {
            // The unique index on (tenant_id, product_id) already exists from migration 1.
            $table->dropPrimary();
            $table->dropColumn('id');
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->primary(['tenant_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->dropPrimary();
        });

        Schema::table('tenant_product', function (Blueprint $table): void {
            $table->uuid('id')->primary();
        });
    }
};
