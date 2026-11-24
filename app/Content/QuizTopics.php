<?php

namespace App\Content;

/**
 * Quiz topics: /quizzes/topic/{key} pages, Question Bank filters and import mapping.
 * opentdb = Open Trivia DB category ids; otqa = OpenTriviaQA category files.
 */
class QuizTopics
{
    public const ALL = [
        'general' => [
            'label' => 'General Knowledge', 'emoji' => '🧠', 'opentdb' => [9], 'otqa' => ['general'],
            'intro' => 'Mixed questions on everyday facts, words, money, time and the world around us. A good warm-up if you are new to quizzes, and every answer is explained when you finish.',
        ],
        'geography' => [
            'label' => 'Geography', 'emoji' => '🌍', 'opentdb' => [22], 'otqa' => ['geography'],
            'intro' => 'Countries, capitals, rivers, mountains and oceans. Test how well you know the map, then read the short explanations to learn one new fact per question.',
        ],
        'history' => [
            'label' => 'History', 'emoji' => '🏛️', 'opentdb' => [23], 'otqa' => ['history'],
            'intro' => 'Empires, wars, explorers and turning points from ancient times to the 20th century. Each answer comes with the date or story behind it.',
        ],
        'science' => [
            'label' => 'Science', 'emoji' => '🔬', 'opentdb' => [17], 'otqa' => ['science-technology'],
            'intro' => 'Biology, chemistry, physics and space. Questions range from school basics to tricky facts, and the explanations say why the answer is right.',
        ],
        'animals' => [
            'label' => 'Animals', 'emoji' => '🐾', 'opentdb' => [27], 'otqa' => ['animals'],
            'intro' => 'Wild animals, birds, sea life and pets: names, homes, habits and records from the animal kingdom.',
        ],
        'maths' => [
            'label' => 'Maths', 'emoji' => '➗', 'opentdb' => [19], 'otqa' => [],
            'intro' => 'Numbers, shapes, angles and famous mathematicians. Some questions need a quick sum in your head; the explanations show the working.',
        ],
        'computers' => [
            'label' => 'Computers & Internet', 'emoji' => '💻', 'opentdb' => [18], 'otqa' => [],
            'intro' => 'Hardware, programming, the web and tech history. Useful for students and anyone who works on a computer every day.',
        ],
        'sports' => [
            'label' => 'Sports', 'emoji' => '🏆', 'opentdb' => [21], 'otqa' => ['sports'],
            'intro' => 'Football, cricket, tennis, the Olympics and more. Questions cover rules, records and famous moments.',
        ],
    ];

    public static function label(?string $topic): string
    {
        return self::ALL[$topic]['label'] ?? ucfirst((string) $topic);
    }

    public static function exists(?string $topic): bool
    {
        return $topic !== null && isset(self::ALL[$topic]);
    }

    /** CC BY-SA 4.0 credit lines, as required by both question sources. */
    public const CREDITS = [
        'opentdb' => 'Questions adapted from Open Trivia Database (https://opentdb.com), licensed CC BY-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/). Explanations and edits by BuntyFun, shared under the same licence.',
        'opentriviaqa' => 'Questions adapted from OpenTriviaQA by uberspot (https://github.com/uberspot/OpenTriviaQA), licensed CC BY-SA 4.0 (https://creativecommons.org/licenses/by-sa/4.0/). Explanations and edits by BuntyFun, shared under the same licence.',
    ];
}
