<?php

namespace Database\Factories;

use App\Models\Report;
use App\Models\ReportSection;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReportSectionFactory extends Factory
{
    protected $model = ReportSection::class;

    public function definition(): array
    {
        return [
            'report_id' => Report::factory(),
            'title' => $this->faker->sentence(),
            'content' => [
                'content' => '<p>' . $this->faker->paragraph() . '</p>',
                'plainText' => $this->faker->paragraph()
            ],
            'order' => $this->faker->numberBetween(1, 10),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
} 