<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Content library (11-25-2026): trivia question bank, quotes, daily features,
 * quiz topics, 7 new built-in games and the import/daily automation jobs.
 * Every imported item starts in review; nothing here publishes content.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('topic', 30)->nullable()->after('category_id')->index();
        });

        Schema::create('trivia_questions', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20);                 // opentdb | opentriviaqa | buntyfun
            $table->string('source_ref', 190)->nullable();
            $table->char('hash', 40)->unique();           // normalised prompt + answer: duplicate guard
            $table->string('topic', 30)->index();
            $table->string('difficulty', 10)->default('medium');
            $table->text('prompt');
            $table->string('correct_answer', 255);
            $table->text('wrong_answers');                // JSON list of 3
            $table->text('explanation')->nullable();
            $table->string('status', 20)->default('in_review')->index(); // in_review | approved | rejected
            $table->text('flags')->nullable();            // JSON list of validator warnings
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
            $table->index(['topic', 'status']);
        });

        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->text('body');
            $table->string('author', 120);
            $table->string('author_slug', 140)->index();
            $table->string('topic', 30)->index();         // main tag, used for the topic page
            $table->string('tags', 255)->nullable();      // comma list
            $table->char('hash', 40)->unique();
            $table->string('source', 30)->default('buntyfun');
            $table->string('source_ref', 60)->nullable();
            $table->string('status', 20)->default('in_review')->index(); // in_review | published | rejected
            $table->text('flags')->nullable();
            $table->timestamps();
        });

        Schema::create('daily_features', function (Blueprint $table) {
            $table->id();
            $table->date('feature_date');
            $table->string('type', 20);                   // quiz | quote | game
            $table->unsignedBigInteger('item_id');
            $table->boolean('pinned')->default(false);    // set by an admin: the auto-picker never changes it
            $table->timestamps();
            $table->unique(['feature_date', 'type']);
        });

        // Existing hand-written quizzes get a topic so they appear on the topic pages.
        foreach ([
            'solar-system-quiz' => 'science', 'world-capitals-quiz' => 'geography', 'human-body-quiz' => 'science',
            'pakistan-general-knowledge-quiz' => 'general', 'islamic-history-quiz' => 'history', 'cricket-quiz' => 'sports',
            'world-flags-quiz' => 'geography', 'science-quiz-for-kids' => 'science', 'english-vocabulary-quiz' => 'general',
        ] as $slug => $topic) {
            DB::table('quizzes')->where('slug', $slug)->whereNull('topic')->update(['topic' => $topic]);
        }

        $now = now();
        $jobs = [
            ['key' => 'import_trivia', 'name' => 'Import trivia questions', 'frequency' => 'daily', 'run_at' => '01:00', 'enabled' => true,
                'description' => 'Fetches new questions from OpenTDB and OpenTriviaQA (CC BY-SA 4.0) into the Question Bank as In review. Duplicates and unsafe topics are skipped; nothing is published.'],
            ['key' => 'build_quizzes', 'name' => 'Build quizzes from approved questions', 'frequency' => 'daily', 'run_at' => '01:30', 'enabled' => true,
                'description' => 'Every topic with 10 approved, unused questions becomes a new quiz saved as In review. An admin publishes it.'],
            ['key' => 'import_quotes', 'name' => 'Import quotes', 'frequency' => 'weekly', 'run_at' => '02:30', 'enabled' => true,
                'description' => 'Adds the next batch from the MIT-licensed QuoteSlate collection as In review. Duplicates are skipped.'],
            ['key' => 'daily_picks', 'name' => 'Daily quiz, quote and game', 'frequency' => 'daily', 'run_at' => '00:05', 'enabled' => true,
                'description' => 'Fills the Daily Schedule for the next 7 days from published content. Days an admin pinned are never changed.'],
            ['key' => 'import_wallpapers', 'name' => 'Import wallpapers (Wikimedia + NASA)', 'frequency' => 'weekly', 'run_at' => '03:30', 'enabled' => false,
                'description' => 'Runs the saved wallpaper searches (Settings → wallpaper_searches) against Wikimedia Commons quality images and NASA. Saves In review only. Off until you turn it on.'],
        ];
        foreach ($jobs as $job) {
            DB::table('automation_jobs')->insertOrIgnore($job + ['max_retries' => 3, 'created_at' => $now, 'updated_at' => $now]);
        }

        $category = DB::table('categories')->where('slug', 'games')->value('id');
        if ($category) {
            foreach (\App\Content\GameGuides::NEW_GAMES as $slug => [$title, $audience]) {
                if (! DB::table('games')->where('slug', $slug)->exists()) {
                    DB::table('games')->insert([
                        'category_id' => $category, 'slug' => $slug, 'title' => $title, 'status' => 'in_review',
                        'config_json' => json_encode(['audience' => $audience, 'source' => 'BuntyFun original']),
                        'created_at' => $now, 'updated_at' => $now,
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        DB::table('automation_jobs')->whereIn('key', ['import_trivia', 'build_quizzes', 'import_quotes', 'daily_picks', 'import_wallpapers'])->delete();
        DB::table('games')->whereIn('slug', array_keys(\App\Content\GameGuides::NEW_GAMES))->where('status', '!=', 'published')->delete();
        Schema::dropIfExists('daily_features');
        Schema::dropIfExists('quotes');
        Schema::dropIfExists('trivia_questions');
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropIndex(['topic']);
            $table->dropColumn('topic');
        });
    }
};
