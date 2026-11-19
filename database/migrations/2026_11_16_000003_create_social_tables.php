<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Social publishing (Facebook Page + Instagram via the official Graph API).
 * Live: docs/sql/2026_11_16_social.mysql.sql (no SSH). Keep both in step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'instagram']);
            $table->string('external_id', 64);
            $table->string('name');
            $table->string('username')->nullable();
            $table->text('access_token');            // encrypted (APP_KEY)
            $table->timestamp('token_expires_at')->nullable();
            $table->string('page_id', 64)->nullable(); // for Instagram: the linked Facebook Page
            $table->boolean('enabled')->default(true);
            $table->string('last_error', 300)->nullable();
            $table->unsignedBigInteger('connected_by')->nullable();
            $table->timestamps();
            $table->unique(['platform', 'external_id']);
        });

        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->enum('platform', ['facebook', 'instagram']);
            $table->unsignedBigInteger('social_account_id');
            $table->string('source_type', 40)->nullable();
            $table->unsignedBigInteger('source_id')->nullable();
            $table->text('caption');
            $table->string('link', 500)->nullable();
            $table->string('image_url', 500)->nullable();
            $table->enum('status', ['draft', 'approved', 'publishing', 'published', 'failed', 'cancelled'])->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->string('external_id', 100)->nullable();
            $table->string('permalink', 500)->nullable();
            $table->string('error', 500)->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamps();
            $table->index(['status', 'scheduled_at']);
            $table->index(['source_type', 'source_id']);
        });

        $now = now();
        DB::table('automation_jobs')->insert([
            'key' => 'social_publish', 'name' => 'Publish approved social posts', 'frequency' => 'every_5_minutes', 'run_at' => null,
            'description' => 'Posts approved Facebook/Instagram posts whose time has come, through the official Graph API. Failed posts retry 3 times.',
            'enabled' => true, 'max_retries' => 3, 'created_at' => $now, 'updated_at' => $now,
        ]);
    }

    public function down(): void
    {
        DB::table('automation_jobs')->where('key', 'social_publish')->delete();
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('social_accounts');
    }
};
