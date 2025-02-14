<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class SeoLogFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'user_id' => User::factory()->seoProvider(),
            'log_type' => fake()->randomElement(array_keys(\App\Models\SeoLog::TYPES)),
            'title' => fake()->sentence(),
            'date' => fake()->dateTimeBetween('-1 month', 'now'),
            'content' => [
                'content' => '<p>' . fake()->paragraph() . '</p>',
                'plainText' => fake()->paragraph()
            ],
            'action_items' => [
                'content' => '<ul><li>' . fake()->sentence() . '</li></ul>',
                'plainText' => fake()->sentence()
            ],
            'recommendations' => [
                'content' => '<ul><li>' . fake()->sentence() . '</li></ul>',
                'plainText' => fake()->sentence()
            ],
        ];
    }

    public function technical(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'technical'
        ]);
    }

    public function analytics(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'analytics'
        ]);
    }

    public function offPage(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'off_page'
        ]);
    }

    public function onPage(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'on_page'
        ]);
    }

    public function local(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'local'
        ]);
    }

    public function content(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'content'
        ]);
    }

    public function social(): self
    {
        return $this->state(fn (array $attributes) => [
            'log_type' => 'social'
        ]);
    }
} 