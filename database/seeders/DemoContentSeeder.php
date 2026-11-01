<?php

namespace Database\Seeders;

use App\Models\BirthdayMessage;
use App\Models\Category;
use App\Models\Game;
use App\Models\NameEntry;
use App\Models\Quiz;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    /**
     * Demo rows to exercise the schema end-to-end. Everything here is seeded as
     * draft/in_review, not published — real content needs an editor's sign-off
     * first (name_entries especially: source_ref must be verified, not assumed).
     */
    public function run(): void
    {
        $games = Category::where('slug', 'games')->firstOrFail();
        $quizzes = Category::where('slug', 'quizzes')->firstOrFail();
        $wishes = Category::where('slug', 'birthday-wishes')->firstOrFail();

        $quiz = Quiz::updateOrCreate(
            ['slug' => 'how-well-do-you-know-your-best-friend'],
            [
                'category_id' => $quizzes->id,
                'title' => 'How Well Do You Know Your Best Friend?',
                'language' => 'en',
                'status' => 'in_review',
            ]
        );

        $q1 = $quiz->questions()->updateOrCreate(
            ['position' => 1],
            ['prompt' => "What's their go-to comfort food?"]
        );
        $q1->answers()->updateOrCreate(['position' => 1], ['label' => 'Pizza', 'score_value' => 1]);
        $q1->answers()->updateOrCreate(['position' => 2], ['label' => 'Ice cream', 'score_value' => 2]);

        $q2 = $quiz->questions()->updateOrCreate(
            ['position' => 2],
            ['prompt' => 'Which trip would they pick?']
        );
        $q2->answers()->updateOrCreate(['position' => 1], ['label' => 'Beach', 'score_value' => 1]);
        $q2->answers()->updateOrCreate(['position' => 2], ['label' => 'Mountains', 'score_value' => 2]);

        // Games are self-contained client-side mechanics, not editorial prose — safe to
        // publish directly, unlike the content types below that need a human sign-off.
        foreach ([
            'tic-tac-toe' => 'Tic-Tac-Toe',
            'rock-paper-scissors' => 'Rock Paper Scissors',
            'word-scramble' => 'Word Scramble',
            'memory-match' => 'Memory Match',
            'daily-brain-challenge' => 'Daily Brain Challenge',
        ] as $slug => $title) {
            Game::updateOrCreate(
                ['slug' => $slug],
                ['category_id' => $games->id, 'title' => $title, 'status' => 'published']
            );
        }

        BirthdayMessage::updateOrCreate(
            ['slug' => 'best-friend-funny-01'],
            [
                'category_id' => $wishes->id,
                'relationship' => 'best_friend',
                'tone' => 'funny',
                'language' => 'en',
                'body' => 'Happy birthday to the person who knows all my secrets and still hangs out with me anyway!',
                'status' => 'in_review',
            ]
        );

        // Well-documented standard Arabic etymology, not a claim of Quranic origin.
        // Seeded as in_review — an editor with a verified reference must approve before publish.
        foreach ([
            [
                'name_en' => 'Muhammad',
                'name_ar' => 'محمد',
                'name_ur' => 'محمد',
                'gender' => 'male',
                'meaning' => 'Praiseworthy, one who is praised',
                'origin' => 'Arabic',
                'source_ref' => 'PLACEHOLDER — verify against a published Arabic lexicon before publishing',
                'slug' => 'muhammad',
            ],
            [
                'name_en' => 'Fatima',
                'name_ar' => 'فاطمة',
                'name_ur' => 'فاطمہ',
                'gender' => 'female',
                'meaning' => "One who weans/abstains; a name of the Prophet's daughter",
                'origin' => 'Arabic',
                'source_ref' => 'PLACEHOLDER — verify against a published Arabic lexicon before publishing',
                'slug' => 'fatima',
            ],
        ] as $name) {
            NameEntry::updateOrCreate(['slug' => $name['slug']], [...$name, 'status' => 'in_review']);
        }
    }
}
