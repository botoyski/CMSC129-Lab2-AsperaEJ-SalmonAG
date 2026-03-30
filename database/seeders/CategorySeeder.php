<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'General', 'slug' => 'general'],
            ['name' => 'Academic', 'slug' => 'academic'],
            ['name' => 'Personal', 'slug' => 'personal'],
            ['name' => 'Project', 'slug' => 'project'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(['slug' => $category['slug']], $category);
        }
    }
}
