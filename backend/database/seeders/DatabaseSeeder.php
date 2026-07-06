<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create categories
        $categories = [
            ['name' => 'Teknologi', 'slug' => 'teknologi'],
            ['name' => 'Kemanusiaan', 'slug' => 'kemanusiaan'],
            ['name' => 'Pendidikan', 'slug' => 'pendidikan'],
            ['name' => 'Kreatif', 'slug' => 'kreatif'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }

        $this->call(UserSeeder::class);
    }
}
