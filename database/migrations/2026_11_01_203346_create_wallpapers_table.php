<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallpapers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            // nature, landscapes, abstract, minimalist, technology, animals, space, seasonal
            $table->string('category');
            $table->enum('orientation', ['desktop', 'mobile', 'both'])->default('both');
            $table->unsignedInteger('width');
            $table->unsignedInteger('height');
            $table->string('file_path'); // storage/app/public/wallpapers/...
            // public_domain, cc0, cc_by, original — "original" means we/the site made it.
            $table->string('license');
            // Required unless license is public_domain or original. Who made/owns it,
            // and where it came from — never redistribute without this on record.
            $table->string('attribution')->nullable();
            $table->string('source_url')->nullable();
            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->timestamps();

            $table->index('category');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallpapers');
    }
};
