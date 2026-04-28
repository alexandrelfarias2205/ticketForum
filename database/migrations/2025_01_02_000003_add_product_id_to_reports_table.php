<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->uuid('product_id')->nullable()->after('tenant_id');

            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropIndex(['product_id']);
            $table->dropColumn('product_id');
        });
    }
};
