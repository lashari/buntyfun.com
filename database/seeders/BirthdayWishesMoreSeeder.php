<?php

namespace Database\Seeders;

use App\Content\BirthdayWishesMore;
use App\Models\BirthdayMessage;
use App\Models\Category;
use Illuminate\Database\Seeder;

/** Hand-written hub wishes (App\Content\BirthdayWishesMore). Create-only: never changes existing rows. */
class BirthdayWishesMoreSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'birthday-wishes')->firstOrFail();
        foreach (BirthdayWishesMore::rows() as $row) {
            BirthdayMessage::firstOrCreate(['slug' => $row['slug']], $row + ['category_id' => $category->id, 'status' => 'published']);
        }
    }
}
