<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'name' => fake()->company() . ' Website',
            'description' => fake()->paragraph(),
            'website_url' => fake()->url(),
            'status' => fake()->randomElement(['active', 'paused', 'completed']),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'details' => [
                'content' => fake()->paragraphs(3, true),
            ],
        ];
    }

    public function active(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function paused(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paused',
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }
} 