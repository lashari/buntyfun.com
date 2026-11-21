<?php

namespace App\Content;

/** How-to-play text for each browser game; keys are games.slug. Describes the code as built. */
class GameGuides
{
    public const ALL = [
        'tic-tac-toe' => [
            'summary' => 'The classic three-in-a-row game for two players on one device.',
            'how' => [
                'Two players share the same screen and take turns. X always goes first.',
                'Tap an empty square to place your mark. The turn passes to the other player automatically.',
                'Get three of your marks in a row (across, down or diagonally) to win.',
                'If all nine squares fill up with no line of three, the game is a draw. Press New Game to play again.',
            ],
            'scoring' => 'There are no points: each round ends in a win for X, a win for O, or a draw. Keep your own tally if you play a best-of-five.',
            'tips' => [
                'The centre square is part of four possible lines, more than any other square, so it is the strongest first move.',
                'Corners are part of three lines each; edges only two.',
                'Try to create a "fork": a move that threatens two lines at once, so your opponent can only block one.',
                'Between two careful players, tic-tac-toe always ends in a draw. Winning depends on your opponent making a mistake.',
            ],
        ],
        'rock-paper-scissors' => [
            'summary' => 'Play the hand game against the computer, one throw at a time.',
            'how' => [
                'Tap rock (✊), paper (✋) or scissors (✌️).',
                'The computer picks at the same moment, completely at random.',
                'Rock beats scissors, scissors beat paper, paper beats rock. The same choice is a draw.',
            ],
            'scoring' => 'The counter at the top tracks your wins, losses and draws for this visit. It resets when you reload the page.',
            'tips' => [
                'Because the computer chooses randomly, no strategy can beat it in the long run. Over many rounds you should win, lose and draw about a third of the time each.',
                'Against people it is different: many players repeat a winning throw, so it can pay to play what beats their last winning choice.',
                'Use it to settle small decisions fairly: best of three, then best of five.',
            ],
        ],
        'memory-match' => [
            'summary' => 'Flip cards two at a time and find all eight matching pairs.',
            'how' => [
                'Sixteen cards are dealt face down: eight pairs of party icons, shuffled each game.',
                'Tap a card to turn it over, then tap a second card.',
                'If the two icons match, they stay face up. If not, both turn back over after a moment.',
                'Clear the board by finding all eight pairs. Press New Game to reshuffle.',
            ],
            'scoring' => 'Every pair of cards you turn over counts as one move. The perfect score is 8 moves, which needs luck; under 20 is a good game.',
            'tips' => [
                'Work through the board in order (row by row) on your first pass, so you remember positions more easily.',
                'Say the icon and its position in your head ("cake, top left") to help memory.',
                'When you turn a new card, first think whether you have already seen its partner before turning a random second card.',
            ],
        ],
        'word-scramble' => [
            'summary' => 'Unscramble five everyday English words, one per round.',
            'how' => [
                'Each round shows the letters of a word in a mixed-up order.',
                'Type the word you think it is and press Submit (or Enter).',
                'You get one guess per word. The answer is shown if you get it wrong, and the next word follows.',
                'After five rounds you see your final score. Press Play again for a new set.',
            ],
            'scoring' => 'One point per correctly unscrambled word, out of 5. Capital letters and spaces around your answer do not matter.',
            'tips' => [
                'Look for common endings such as -ing, -ion, -ory or -ine and set them aside first.',
                'Find the vowels and try them between consonant pairs like "sh", "th" or "ch".',
                'Say possible syllables out loud; your ear often spots the word before your eyes do.',
            ],
        ],
        'daily-brain-challenge' => [
            'summary' => 'One riddle a day, with the answer explained once you choose.',
            'how' => [
                'A new riddle appears each day (the date is shown under the title). Everyone sees the same riddle on the same day.',
                'Pick one of the four answers. You get one try.',
                'The correct answer turns green, and a short explanation shows why it works.',
            ],
            'scoring' => 'There are no points; it is a daily habit, not a contest. Come back tomorrow for the next riddle.',
            'tips' => [
                'Riddles often use a word in two senses ("keys" on a keyboard, the "neck" of a bottle). Ask yourself which words could mean something else.',
                'Read every option before choosing; the obvious answer is often a trap.',
                'Share the riddle with a friend before telling them the answer.',
            ],
        ],
    ];

    public static function for(string $slug): ?array
    {
        return self::ALL[$slug] ?? OssGames::for($slug);
    }
}
