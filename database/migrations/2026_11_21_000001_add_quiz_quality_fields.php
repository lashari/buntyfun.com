<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('difficulty', 10)->nullable()->after('intro');
            $table->text('sources')->nullable()->after('difficulty');
            $table->text('outcomes')->nullable()->after('sources');
            $table->date('reviewed_at')->nullable()->after('outcomes');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['difficulty', 'sources', 'outcomes', 'reviewed_at']);
        });
    }
};
