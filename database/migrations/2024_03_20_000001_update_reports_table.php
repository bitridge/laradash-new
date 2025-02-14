<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            // Drop existing columns if they exist
            $table->dropColumn(['description', 'status']);
            
            // Add new columns
            $table->json('description')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('description');
            $table->string('status')->default('draft')->after('title');
            $table->text('description')->nullable()->after('status');
        });
    }
}; 