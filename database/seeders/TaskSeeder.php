<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::first();

        if (! $user) {
            return;
        }

        $categoryIds = Category::query()->pluck('id')->all();

        Task::factory(20)
            ->state(fn () => [
                'user_id' => $user->id,
                'category_id' => fake()->randomElement($categoryIds),
            ])
            ->create();
    }
}
