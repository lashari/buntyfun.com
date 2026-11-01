<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Release 1 departments. Condolence, Urdu, Wallpapers and AI Fun are added
     * as their own Release 2/3 work lands — not seeded here.
     */
    public function run(): void
    {
        $departments = [
            ['department' => 'games', 'slug' => 'games', 'name' => 'Games'],
            ['department' => 'quizzes', 'slug' => 'quizzes', 'name' => 'Quizzes'],
            ['department' => 'birthday_wishes', 'slug' => 'birthday-wishes', 'name' => 'Birthday Wishes'],
            ['department' => 'islamic_names', 'slug' => 'islamic-names', 'name' => 'Islamic Names'],
        ];

        foreach ($departments as $department) {
            Category::updateOrCreate(['slug' => $department['slug']], $department);
        }
    }
}
