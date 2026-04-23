<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_labels', function (Blueprint $table) {
            $table->uuid('report_id');
            $table->uuid('label_id');
            $table->primary(['report_id', 'label_id']);

            $table->foreign('report_id')->references('id')->on('reports')->onDelete('cascade');
            $table->foreign('label_id')->references('id')->on('labels')->onDelete('cascade');

            $table->index('report_id');
            $table->index('label_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_labels');
    }
};
