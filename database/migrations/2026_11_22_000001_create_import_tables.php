<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // One row per "Start import" click in Admin → Import.
        Schema::create('import_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('content_type', 30);          // wallpaper (quotes/names later)
            $table->string('source', 30);                // nasa, unsplash
            $table->string('query', 200);
            $table->string('category', 60);
            $table->unsignedSmallInteger('target_count');
            $table->unsignedSmallInteger('page')->default(1);
            $table->json('options')->nullable();         // orientation, min_width, status
            $table->longText('candidates')->nullable();  // JSON list fetched from the source, worked through by `cursor`
            $table->unsignedInteger('cursor')->default(0);
            $table->unsignedSmallInteger('saved')->default(0);
            $table->unsignedSmallInteger('skipped')->default(0);
            $table->unsignedSmallInteger('failed')->default(0);
            $table->enum('status', ['running', 'done', 'failed', 'undone'])->default('running');
            $table->string('message', 500)->nullable();
            $table->timestamps();
        });

        Schema::create('import_run_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_run_id')->constrained()->cascadeOnDelete();
            $table->string('source_key', 120);
            $table->string('title')->nullable();
            $table->string('item_type', 40)->nullable();
            $table->unsignedBigInteger('item_id')->nullable();
            $table->enum('status', ['saved', 'skipped', 'failed']);
            $table->string('message', 300)->nullable();
            $table->timestamps();
            $table->index(['item_type', 'item_id']);
        });

        Schema::table('wallpapers', function (Blueprint $table) {
            // e.g. nasa:PIA12345 / unsplash:abc123 — stops the same picture being imported twice.
            $table->string('source_key', 120)->nullable()->unique();
            // Unsplash's API terms require showing their image URL (hotlink), not a re-hosted copy.
            $table->string('remote_url', 1000)->nullable();
            $table->string('download_location', 500)->nullable();
            $table->string('attribution_url', 500)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('wallpapers', function (Blueprint $table) {
            $table->dropUnique(['source_key']);
            $table->dropColumn(['source_key', 'remote_url', 'download_location', 'attribution_url']);
        });
        Schema::dropIfExists('import_run_items');
        Schema::dropIfExists('import_runs');
    }
};
