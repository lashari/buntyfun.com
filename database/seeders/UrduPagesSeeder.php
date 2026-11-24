<?php

namespace Database\Seeders;

use App\Content\UrduPages;
use App\Models\UrduContent;
use Illuminate\Database\Seeder;

/** Hand-written Urdu topic lines (App\Content\UrduPages). Create-only: never changes existing rows. */
class UrduPagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (UrduPages::rows() as $row) {
            UrduContent::firstOrCreate(['slug' => $row['slug']], $row + ['status' => 'published']);
        }
    }
}
