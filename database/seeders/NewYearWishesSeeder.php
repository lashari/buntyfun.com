<?php

namespace Database\Seeders;

use App\Content\NewYearWishes;
use App\Models\Message;
use Illuminate\Database\Seeder;

/**
 * Hand-written New Year wishes (App\Content\NewYearWishes).
 * Creates missing rows only: never changes status or text of rows that already exist,
 * so a re-run cannot unpublish or overwrite edited wishes.
 */
class NewYearWishesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NewYearWishes::rows() as $row) {
            Message::firstOrCreate(['slug' => $row['slug']], $row + ['status' => 'published']);
        }
    }
}
