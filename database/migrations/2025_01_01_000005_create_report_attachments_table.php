<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_attachments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('report_id');
            $table->string('type');
            $table->text('url');
            $table->string('filename')->nullable();
            $table->unsignedInteger('size_bytes')->nullable();
            $table->timestamps();

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
            $table->index('report_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_attachments');
    }
};
