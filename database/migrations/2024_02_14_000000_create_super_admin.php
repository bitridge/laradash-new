<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    public function up(): void
    {
        // Create super admin user
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@laradash.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
    }

    public function down(): void
    {
        // Remove super admin user
        User::where('email', 'admin@laradash.com')->delete();
    }
}; 