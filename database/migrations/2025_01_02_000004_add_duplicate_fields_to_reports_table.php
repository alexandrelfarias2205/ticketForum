<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->boolean('is_duplicate')->default(false)->after('vote_count');
            $table->uuid('duplicate_of_report_id')->nullable()->after('is_duplicate');

            $table->foreign('duplicate_of_report_id')->references('id')->on('reports')->onDelete('set null');
            $table->index('duplicate_of_report_id');
            $table->index(['tenant_id', 'is_duplicate']);
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['duplicate_of_report_id']);
            $table->dropIndex(['duplicate_of_report_id']);
            $table->dropIndex(['tenant_id', 'is_duplicate']);
            $table->dropColumn(['is_duplicate', 'duplicate_of_report_id']);
        });
    }
};
