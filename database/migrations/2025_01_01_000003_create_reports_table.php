<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tenant_id');
            $table->uuid('author_id');
            $table->uuid('reviewer_id')->nullable();
            $table->string('type');
            $table->string('title', 500);
            $table->text('description');
            $table->string('status')->default('pending_review');
            $table->string('enriched_title', 500)->nullable();
            $table->text('enriched_description')->nullable();
            $table->string('external_issue_url')->nullable();
            $table->string('external_issue_id')->nullable();
            $table->string('external_platform')->nullable();
            $table->unsignedInteger('vote_count')->default(0);
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('author_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('reviewer_id')->references('id')->on('users')->onDelete('set null');

            $table->index('tenant_id');
            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'vote_count']);
            $table->index('author_id');
            $table->index('reviewer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
