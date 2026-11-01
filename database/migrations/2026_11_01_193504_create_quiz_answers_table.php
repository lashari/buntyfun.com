<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->unsignedInteger('position')->default(0);
            $table->text('label');
            // Trivia quizzes use is_correct; personality/trait quizzes use score_value. A quiz uses one or the other.
            $table->boolean('is_correct')->nullable();
            $table->integer('score_value')->nullable();
            $table->timestamps();

            $table->index(['quiz_question_id', 'position']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
