<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Academic',
            'Personal',
            'Project',
            'Errands',
            'Health',
            'Finance',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name),
        ];
    }
}
