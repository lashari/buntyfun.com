<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Quiz pages need an intro, and trivia answers an explanation shown on the result page. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->text('intro')->nullable()->after('title');
        });
        Schema::table('quiz_questions', function (Blueprint $table) {
            $table->text('explanation')->nullable()->after('prompt');
        });
    }

    public function down(): void
    {
        Schema::table('quiz_questions', fn (Blueprint $table) => $table->dropColumn('explanation'));
        Schema::table('quizzes', fn (Blueprint $table) => $table->dropColumn('intro'));
    }
};
