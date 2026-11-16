<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/*
 * Several AI keys, managed in Admin → Automation → Settings & API.
 * Live: docs/sql/2026_11_16_ai_api_keys.mysql.sql (no SSH). Keep both in step.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_api_keys', function (Blueprint $table) {
            $table->id();
            $table->enum('provider', ['gemini', 'openai'])->default('gemini');
            $table->string('label', 60);
            $table->text('api_key');               // encrypted with APP_KEY (Laravel 'encrypted' cast)
            $table->string('last4', 4);
            $table->boolean('enabled')->default(true);
            $table->unsignedSmallInteger('priority')->default(10);
            $table->unsignedInteger('use_count')->default(0);
            $table->unsignedSmallInteger('fail_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->string('last_error', 120)->nullable();
            $table->timestamp('cooldown_until')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->index(['provider', 'enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_api_keys');
    }
};
