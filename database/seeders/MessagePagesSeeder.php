<?php

namespace Database\Seeders;

use App\Content\MessagePages;
use App\Models\Message;
use Illuminate\Database\Seeder;

/**
 * Hand-written category messages (App\Content\MessagePages).
 * Creates missing rows only: never changes status or text of rows that already exist.
 */
class MessagePagesSeeder extends Seeder
{
    public function run(): void
    {
        foreach (MessagePages::rows() as $row) {
            Message::firstOrCreate(['slug' => $row['slug']], $row + ['status' => 'published']);
        }
    }
}
