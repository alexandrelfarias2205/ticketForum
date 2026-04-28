<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->string('agent_branch')->nullable()->after('external_platform');
            $table->string('merge_request_url')->nullable()->after('agent_branch');

            $table->index('agent_branch');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['agent_branch']);
            $table->dropColumn(['agent_branch', 'merge_request_url']);
        });
    }
};
