<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Islamic knowledge + Urdu poetry library (11-26-2026). Same schema as
 * deploy SQL 2026_11_26_library_01_alter.sql + _02_tables.sql.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('name_entries', function (Blueprint $table) {
            $table->text('meaning_ur')->nullable()->after('meaning');
            $table->string('category', 20)->default('general')->after('origin');
            $table->string('quranic_ref', 190)->nullable()->after('category');
            $table->string('verification', 20)->default('needs_review')->after('source_ref');
            $table->index('category');
        });

        Schema::create('allah_names', function (Blueprint $table) {
            $table->id();
            $table->unsignedTinyInteger('number')->unique();
            $table->string('arabic', 80);
            $table->string('transliteration', 80);
            $table->string('meaning_en', 190);
            $table->string('meaning_ur', 190)->nullable();
            $table->string('quran_ref', 120)->nullable();
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
        });

        Schema::create('duas', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 160)->unique();
            $table->string('title', 190);
            $table->string('category', 30)->index();
            $table->text('arabic');
            $table->text('transliteration')->nullable();
            $table->text('translation_en');
            $table->text('translation_ur')->nullable();
            $table->string('reference', 190);
            $table->string('source_url', 255)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
        });

        Schema::create('poets', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name_en', 120);
            $table->string('name_ur', 120);
            $table->smallInteger('born')->nullable();
            $table->smallInteger('died')->nullable();
            $table->text('bio_en')->nullable();
            $table->text('bio_ur')->nullable();
            $table->string('wikipedia', 255)->nullable();
            $table->string('status', 20)->default('published')->index();
            $table->timestamps();
        });

        Schema::create('poems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poet_id')->constrained('poets');
            $table->string('slug', 120)->unique();
            $table->string('title', 255);
            $table->string('form', 20)->index();
            $table->string('mood', 20)->nullable()->index();
            $table->string('book', 190)->nullable();
            $table->text('body');
            $table->unsignedSmallInteger('line_count')->default(0);
            $table->char('hash', 40)->unique();
            $table->string('source', 40)->default('divan-data');
            $table->string('source_url', 500)->nullable();
            $table->string('license', 40)->nullable();
            $table->string('status', 20)->default('in_review')->index();
            $table->timestamps();
            $table->index(['poet_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poems');
        Schema::dropIfExists('poets');
        Schema::dropIfExists('duas');
        Schema::dropIfExists('allah_names');
        Schema::table('name_entries', function (Blueprint $table) {
            $table->dropIndex(['category']);
            $table->dropColumn(['meaning_ur', 'category', 'quranic_ref', 'verification']);
        });
    }
};
