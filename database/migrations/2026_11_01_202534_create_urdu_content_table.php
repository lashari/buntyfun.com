<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('urdu_contents', function (Blueprint $table) {
            $table->id();
            // friendship, jokes, greetings, love, inspirational, festival, quotes.
            // Poetry is deliberately excluded for now — copyright risk on anything
            // not demonstrably original.
            $table->string('category');
            $table->text('body');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('urdu_contents');
    }
};
