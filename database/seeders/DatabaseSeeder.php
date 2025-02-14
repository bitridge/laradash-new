<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SeoLog;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create admin user
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // Create SEO providers
        $seoProviders = User::factory()->count(5)->seoProvider()->create();

        // Create customers with their projects and assign SEO providers
        $customers = Customer::factory()
            ->count(10)
            ->create()
            ->each(function ($customer) use ($seoProviders) {
                // Create 1-3 projects for each customer
                $projects = Project::factory()
                    ->count(rand(1, 3))
                    ->create(['customer_id' => $customer->id]);

                // For each project
                $projects->each(function ($project) use ($seoProviders) {
                    // Assign 1-2 random SEO providers to the project
                    $project->seoProviders()->attach(
                        $seoProviders->random(rand(1, 2))->pluck('id')->toArray()
                    );

                    // Create 3-8 SEO logs for each project
                    SeoLog::factory()
                        ->count(rand(3, 8))
                        ->create([
                            'project_id' => $project->id,
                            'user_id' => $project->seoProviders->random()->id,
                        ]);
                });
            });
    }
}
