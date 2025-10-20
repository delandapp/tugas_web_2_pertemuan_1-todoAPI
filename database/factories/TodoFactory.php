<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\TodoStatus;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Todo>
 */
class TodoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $status = fake()->randomElement(TodoStatus::values());

        return [
            'uuid' => fake()->uuid(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => $status,
            'priority' => fake()->numberBetween(1, 5),
            'due_date' => fake()->optional()->dateTimeBetween('now', '+3 months'),
            'completed_at' => in_array($status, [TodoStatus::Completed->value, TodoStatus::Archived->value], true)
                ? fake()->dateTimeBetween('-1 month', 'now')
                : null,
        ];
    }
}
