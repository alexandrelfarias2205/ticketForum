<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_jobs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('platform');
            $table->string('status')->default('pending');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->string('external_id')->nullable();
            $table->jsonb('response_payload')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');

            $table->index('report_id');
            $table->index(['report_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_jobs');
    }
};
