<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/*
 * Automation core (16 Nov 2026). Live has no SSH: the same change ships as
 * docs/sql/2026_11_16_automation_core.mysql.sql, which the owner runs in phpMyAdmin.
 * Keep the two in step.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Per-item SEO + scheduling, shared by every content type (polymorphic, so no
        // ALTERs on the seven content tables).
        Schema::create('content_meta', function (Blueprint $table) {
            $table->id();
            $table->string('metable_type', 40);
            $table->unsignedBigInteger('metable_id');
            $table->string('seo_title', 70)->nullable();
            $table->string('seo_description', 170)->nullable();
            $table->string('og_image')->nullable();
            $table->json('faq')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_trending')->default(false);
            $table->boolean('noindex')->default(false);
            $table->timestamp('publish_at')->nullable();
            $table->timestamps();
            $table->unique(['metable_type', 'metable_id']);
            $table->index('publish_at');
        });

        // English (and Urdu) messages & quotes: greetings, Eid, good morning, condolence...
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->string('category', 40);
            $table->string('language', 2)->default('en');
            $table->string('title')->nullable();
            $table->text('body');
            $table->string('slug')->unique();
            $table->enum('status', ['draft', 'in_review', 'published'])->default('draft');
            $table->timestamps();
            $table->index(['category', 'status']);
        });

        Schema::create('ai_drafts', function (Blueprint $table) {
            $table->id();
            $table->string('content_type', 30);
            $table->json('params')->nullable();
            $table->text('prompt');
            $table->longText('raw_output')->nullable();
            $table->json('payload')->nullable();
            $table->unsignedTinyInteger('quality_score')->nullable();
            $table->json('checks')->nullable();
            $table->enum('status', ['generated', 'failed', 'approved', 'rejected'])->default('generated');
            $table->string('target_type', 40)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->string('error', 500)->nullable();
            $table->unsignedInteger('input_tokens')->nullable();
            $table->unsignedInteger('output_tokens')->nullable();
            $table->decimal('cost_usd', 10, 6)->nullable();
            $table->unsignedBigInteger('automation_run_id')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->string('review_note', 500)->nullable();
            $table->timestamps();
            $table->index(['status', 'content_type']);
        });

        Schema::create('automation_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('key', 40)->unique();
            $table->string('name');
            $table->string('description', 500)->nullable();
            $table->boolean('enabled')->default(true);
            $table->enum('frequency', ['every_5_minutes', 'hourly', 'daily', 'weekly'])->default('daily');
            $table->string('run_at', 5)->nullable(); // HH:MM in app timezone, daily/weekly only
            $table->unsignedTinyInteger('max_retries')->default(3);
            $table->unsignedTinyInteger('consecutive_failures')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->string('last_status', 10)->nullable();
            $table->timestamp('next_run_at')->nullable();
            $table->timestamps();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->id();
            $table->string('job_key', 40);
            $table->string('triggered_by', 40)->default('cron');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->enum('status', ['running', 'success', 'failed', 'skipped'])->default('running');
            $table->unsignedInteger('items')->default(0);
            $table->text('message')->nullable();
            $table->json('details')->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->timestamps();
            $table->index(['job_key', 'started_at']);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key', 60)->primary();
            $table->json('value')->nullable();
            $table->timestamps();
        });

        // Automation writes audit rows with no human user.
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
        });

        $now = now();
        DB::table('automation_jobs')->insert(array_map(fn ($j) => $j + [
            'enabled' => true, 'max_retries' => 3, 'created_at' => $now, 'updated_at' => $now,
        ], [
            ['key' => 'publish_scheduled', 'name' => 'Publish scheduled content', 'frequency' => 'every_5_minutes', 'run_at' => null,
                'description' => 'Publishes approved items whose publish time has come. Sitemap and RSS are built live from the database, so they update at once.'],
            ['key' => 'generate_drafts', 'name' => 'Daily ideas + AI drafts', 'frequency' => 'daily', 'run_at' => '02:00',
                'description' => 'Picks content gaps by category priority and recent views, writes AI drafts, runs quality checks. Drafts wait for approval; nothing is published.'],
            ['key' => 'health_report', 'name' => 'Database + automation health', 'frequency' => 'daily', 'run_at' => '06:00',
                'description' => 'Counts content, failed queue jobs, failed runs, AI spend and drafts waiting.'],
            ['key' => 'broken_links', 'name' => 'Broken-link check', 'frequency' => 'weekly', 'run_at' => '03:00',
                'description' => 'Requests every sitemap URL and lists any that do not return 200.'],
            ['key' => 'content_review', 'name' => 'Expired / weak content review', 'frequency' => 'weekly', 'run_at' => '04:00',
                'description' => 'Lists seasonal items older than 11 months and published items with no views in 90 days. Report only.'],
        ]));
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_jobs');
        Schema::dropIfExists('ai_drafts');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('content_meta');
    }
};
