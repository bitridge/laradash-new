<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportFactory extends Factory
{
    protected $model = Report::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'title' => $this->faker->sentence(),
            'description' => [
                'content' => '<p>' . $this->faker->paragraph() . '</p>',
                'plainText' => $this->faker->paragraph()
            ],
            'generated_by' => User::factory(),
            'generated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 