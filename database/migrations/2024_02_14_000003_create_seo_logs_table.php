<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('log_type');
            $table->string('title');
            $table->json('content');
            $table->json('action_items')->nullable();
            $table->json('recommendations')->nullable();
            $table->date('date');
            $table->text('work_description')->nullable();
            $table->json('keywords_targeted')->nullable();
            $table->json('backlinks_created')->nullable();
            $table->json('rankings_improvement')->nullable();
            $table->text('additional_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_logs');
    }
}; 