<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('description');
            $table->foreignId('generated_by')->constrained('users')->cascadeOnDelete();
            $table->string('status')->default('draft');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });

        Schema::create('report_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->json('content');
            $table->integer('order');
            $table->timestamps();
        });

        Schema::create('report_seo_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('seo_log_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['report_id', 'seo_log_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_seo_logs');
        Schema::dropIfExists('report_sections');
        Schema::dropIfExists('reports');
    }
}; 