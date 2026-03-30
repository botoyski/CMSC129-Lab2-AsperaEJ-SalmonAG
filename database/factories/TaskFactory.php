<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Task>
 */
class TaskFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(4),
            'description' => fake()->optional()->paragraph(),
            'status' => fake()->randomElement(['Not Started', 'In Progress', 'Completed']),
            'priority' => fake()->randomElement(['High', 'Medium', 'Low']),
            'due_date' => fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d'),
            'due_time' => fake()->optional()->time('H:i'),
        ];
    }
}
