<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wallpapers', function (Blueprint $table) {
            // Caption from the source (NASA / Wikimedia). NULL = not fetched yet, '' = source has none.
            $table->text('description')->nullable()->after('title');
        });
    }

    public function down(): void
    {
        Schema::table('wallpapers', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
};
