<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Islamic Names & Audio Library (11-27-2026): Quran text/translations/reciters, naats,
 * one audio_tracks table (name pronunciations + naat recordings), dua grading.
 * Same schema as deploy SQL islamic-audio 01_run_once + 02_tables.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('name_entries', function (Blueprint $table) {
            $table->string('pronunciation', 80)->nullable()->after('name_ur');
        });

        Schema::table('duas', function (Blueprint $table) {
            $table->string('grading', 40)->nullable()->after('source_url');
            $table->string('grading_by', 255)->nullable()->after('grading');
            $table->string('verification', 20)->default('needs_review')->after('grading_by');
        });

        Schema::create('quran_surahs', function (Blueprint $table) {
            $table->unsignedTinyInteger('number')->primary();
            $table->string('slug', 60)->unique();
            $table->string('name_ar', 60);
            $table->string('name_en', 60);
            $table->string('name_meaning', 120);
            $table->string('revelation', 10);
            $table->unsignedSmallInteger('ayah_count');
        });

        Schema::create('quran_ayahs', function (Blueprint $table) {
            $table->unsignedSmallInteger('id')->primary(); // global ayah number 1–6236
            $table->unsignedTinyInteger('surah');
            $table->unsignedSmallInteger('ayah');
            $table->unsignedTinyInteger('juz');
            $table->text('text_uthmani');
            $table->text('text_simple');
            $table->unique(['surah', 'ayah']);
        });

        Schema::create('quran_translations', function (Blueprint $table) {
            $table->id();
            $table->string('code', 40)->unique();
            $table->string('language', 5);
            $table->string('name', 120);
            $table->string('translator', 120);
            $table->string('license', 255);
            $table->string('source_url', 255)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
        });

        Schema::create('quran_translation_texts', function (Blueprint $table) {
            $table->unsignedBigInteger('translation_id');
            $table->unsignedSmallInteger('ayah_id');
            $table->text('text');
            $table->primary(['translation_id', 'ayah_id']);
        });

        Schema::create('quran_reciters', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 60)->unique();
            $table->string('name', 120);
            $table->string('name_ar', 120)->nullable();
            $table->string('country', 60)->nullable();
            $table->string('style', 20)->default('murattal');
            $table->string('url_pattern', 255);
            $table->string('source', 120);
            $table->string('license_note', 255);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
        });

        Schema::create('naats', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 190);
            $table->string('title_ur', 190)->nullable();
            $table->string('category', 30)->index();
            $table->string('poet', 120)->nullable();
            $table->string('reciter', 120)->nullable();
            $table->text('lyrics')->nullable();
            $table->text('description')->nullable();
            $table->string('youtube_id', 20)->nullable();
            $table->string('license', 255)->nullable();
            $table->string('source_url', 255)->nullable();
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
        });

        Schema::create('audio_tracks', function (Blueprint $table) {
            $table->id();
            $table->string('kind', 10); // name | naat
            $table->unsignedBigInteger('ref_id');
            $table->string('file_path', 255)->nullable();
            $table->string('external_url', 500)->nullable();
            $table->string('speaker', 120)->nullable();
            $table->string('license', 255);
            $table->string('source_url', 255)->nullable();
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
            $table->index(['kind', 'ref_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audio_tracks');
        Schema::dropIfExists('naats');
        Schema::dropIfExists('quran_reciters');
        Schema::dropIfExists('quran_translation_texts');
        Schema::dropIfExists('quran_translations');
        Schema::dropIfExists('quran_ayahs');
        Schema::dropIfExists('quran_surahs');
        Schema::table('duas', fn (Blueprint $t) => $t->dropColumn(['grading', 'grading_by', 'verification']));
        Schema::table('name_entries', fn (Blueprint $t) => $t->dropColumn('pronunciation'));
    }
};
