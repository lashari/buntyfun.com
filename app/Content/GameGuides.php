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

        'flag-challenge' => [
            'summary' => 'Guess the country from its flag: 10 rounds, 142 countries, six continents.',
            'how' => [
                'Choose a level: Easy (well-known countries), All countries, or one continent.',
                'A flag appears with four country names. Tap the one it belongs to.',
                'Right answers turn green; a wrong answer shows the correct country so you learn it.',
                'After 10 rounds you see your score, time and longest run of correct answers. Press Play again for a new set.',
            ],
            'scoring' => 'One point per correct flag, out of 10. Your best score for each level is saved in your browser. The timer only counts for bragging rights.',
            'tips' => [
                'The wrong answers usually come from the same continent, so look at details: stars, stripes, emblems and colours.',
                'Many flags share colours. Pan-Arab flags use red, white, green and black; many African flags use red, yellow and green.',
                'Pakistan\'s flag is dark green with a white crescent and star and a white stripe at the hoist; Turkey\'s is red with a white crescent and star.',
                'Play one continent at a time until you get 10/10, then move to All countries.',
            ],
        ],
        'capital-challenge' => [
            'summary' => 'Name the capital city of each country: 10 rounds with easy, world and continent levels.',
            'how' => [
                'Pick a level: Easy, All countries, or one continent.',
                'Each round shows a country and its flag. Choose its capital from four cities.',
                'If you miss, the right capital is shown before the next round.',
                'Finish 10 rounds to see your score and share it.',
            ],
            'scoring' => 'One point per correct capital, out of 10. Best scores are saved per level in your browser.',
            'tips' => [
                'The biggest city is often not the capital: Islamabad not Karachi, Ankara not Istanbul, Canberra not Sydney, Ottawa not Toronto.',
                'Several capitals were purpose-built: Islamabad, Brasília, Canberra, Abuja and Naypyidaw.',
                'Countries with more than one official capital (such as South Africa and Bolivia) are left out to keep every question fair.',
            ],
        ],
        'word-search' => [
            'summary' => 'Find hidden words in a letter grid: 8 themes, three levels, a new puzzle every time.',
            'how' => [
                'Choose a theme (animals, fruits, Pakistan cities, space, sports, weather, food or school) and a level.',
                'Tap the first letter of a word, then tap its last letter. If the letters between them spell a word on the list, it is marked found.',
                'Easy hides words across and down; Medium adds diagonals; Hard hides words in all eight directions, including backwards.',
                'Find every word to stop the clock.',
            ],
            'scoring' => 'Your time is the score: the faster, the better. The best time for each level is saved in your browser.',
            'tips' => [
                'Scan for a rare letter in the word (K, Q, Z, X, J) first; it narrows down the search.',
                'Look for double letters, such as the "EE" in BREEZE or "RR" in CHERRY.',
                'On Hard, read each row backwards as well as forwards.',
                'If you tap a wrong first letter, tap any second cell to reset.',
            ],
        ],
        'maths-race' => [
            'summary' => 'Solve as many sums as you can in 60 seconds, with an on-screen number pad.',
            'how' => [
                'Pick Easy (+ and −), Medium (adds ×) or Hard (adds ÷). Press Start.',
                'Type the answer with the number pad or your keyboard. When you have typed enough digits it is checked automatically.',
                'A green flash means correct and a new sum appears. A red flash means wrong: try again.',
                'When the 60 seconds run out you see your score.',
            ],
            'scoring' => 'One point per correct answer. Wrong answers are counted but do not lose points. Best scores are saved per level.',
            'tips' => [
                'For addition, round one number to a ten and fix it after: 38 + 47 = 40 + 47 − 2 = 85.',
                'Learn the times tables to 12; Medium and Hard use them all the time.',
                'Division questions always have whole-number answers. Think "what times the second number gives the first?"',
                'Playing every day for a week is a proven way to get quicker at mental maths.',
            ],
        ],
        'hangman' => [
            'summary' => 'Guess the hidden word letter by letter before the drawing is finished. Five categories with hints.',
            'how' => [
                'Pick a category. Dashes show how many letters the word has, and a hint is shown below.',
                'Tap a letter (or type it). If it is in the word, it appears in every place it occurs.',
                'Each wrong letter adds a part to the drawing. Six wrong letters and the round is lost.',
                'Guess the whole word to win and grow your streak.',
            ],
            'scoring' => 'Your streak is the number of words guessed in a row. A lost round resets it to zero.',
            'tips' => [
                'Start with the vowels E, A and O, then common consonants such as R, S, T, N and L.',
                'Use the hint and the word length together: 7 letters plus "Tallest animal" can only be GIRAFFE.',
                'Rare letters (Q, Z, X, J) are best saved until you have a strong guess.',
            ],
        ],
        'sudoku' => [
            'summary' => 'Classic 9×9 Sudoku with easy, medium and hard puzzles. Every puzzle has exactly one solution.',
            'how' => [
                'Fill the grid so that every row, every column and every 3×3 box contains the numbers 1 to 9 once.',
                'Tap an empty cell, then tap a number. Tap ⌫ to clear a cell.',
                'Bold numbers are given and cannot be changed. With "Show mistakes" on, a wrong number turns red.',
                'Each new puzzle is generated in your browser and checked to have a single solution.',
            ],
            'scoring' => 'Your time is the score. Mistakes are counted but do not stop the game. The best time per level is saved.',
            'tips' => [
                'Start with rows, columns or boxes that are almost full.',
                'Pick a number and look for the one box where it can still go ("hidden single").',
                'Tapping a filled cell highlights the same number everywhere, which makes gaps easy to spot.',
                'Hard puzzles need you to note where a number could go in two cells; keep scanning before guessing.',
            ],
        ],
        'daily-trivia' => [
            'summary' => 'Five new trivia questions every day with instant answers, explanations and a daily streak.',
            'how' => [
                'Every day at midnight, five questions are picked from our reviewed question bank. Everyone gets the same five that day.',
                'Pick an answer to see straight away if you were right, plus a short explanation.',
                'Answer all five to finish the day and add one to your streak.',
                'Share your score grid (🟩🟥) with friends, then come back tomorrow.',
            ],
            'scoring' => 'One point per correct answer, out of 5. Your streak counts the days in a row you finished. Miss a day and it restarts at 1.',
            'tips' => [
                'Read all four options before answering; one is often a near miss.',
                'The explanations are written to help you remember: you will often see the same fact in our quizzes.',
                'Play at the same time every day, for example with morning tea, to keep your streak going.',
            ],
        ],
        'daily-word' => [
            'summary' => 'Guess the hidden five-letter word in six tries. A new word every day, the same for everyone.',
            'how' => [
                'Type any five-letter word and press Enter (or tap the on-screen keys).',
                'Green means the letter is in the word and in the right place. Yellow means it is in the word but somewhere else. Grey means it is not in the word.',
                'Use the colours to narrow it down. You have six guesses.',
                'Today\'s word is the same for everyone and changes at midnight. Practice gives you a random word whenever you like.',
            ],
            'scoring' => 'Your daily wins, win rate and streak (days in a row solved) are saved in your own browser. Practice games do not count towards the streak.',
            'tips' => [
                'Start with a word that has common letters, such as RAISE, STONE or HEART.',
                'Do not reuse grey letters: each guess should test new letters.',
                'A letter can appear twice. If you are stuck, try doubling one you already found.',
                'Share your colour grid with friends: it shows how you did without giving away the word.',
            ],
        ],
        'emoji-guess' => [
            'summary' => 'Read the emojis and guess the food, saying, place, job or thing they stand for.',
            'how' => [
                'Pick a category, or Mix for a bit of everything.',
                'Each round shows a group of emojis. Choose the answer they describe from four options.',
                'After you answer, the right option turns green. Press Next for the next puzzle.',
                'Ten puzzles make one game.',
            ],
            'scoring' => 'One point per correct answer, out of 10. Your best score for each category is saved in your browser.',
            'tips' => [
                'Say the emojis out loud: many puzzles are sound-alikes or two words joined together.',
                'In Sayings, think of well-known English proverbs and idioms.',
                'Flags in Places are a big clue: look at the colours.',
            ],
        ],
        'typing-test' => [
            'summary' => 'Find out how many words per minute you type, and how accurately.',
            'how' => [
                'Choose Easy, Medium or Hard text and a 30 or 60 second test.',
                'Start typing in the box: the timer starts with your first key.',
                'Correct letters turn green and mistakes turn red. Use Backspace to fix them.',
                'When time runs out you see your speed and accuracy. Pasting is turned off.',
            ],
            'scoring' => 'Speed is counted in words per minute (WPM), where one word is five correctly typed characters. Accuracy is the share of characters you typed correctly. Your best WPM for each level and length is saved in your browser.',
            'tips' => [
                'Accuracy first: fixing mistakes costs more time than typing a little slower.',
                'Keep your fingers on the home row (ASDF and JKL;) and look at the screen, not the keyboard.',
                'An average adult types about 40 WPM; 60+ is fast; 80+ is very fast.',
            ],
        ],
        'reaction-time' => [
            'summary' => 'Test how fast you react: wait for green, then tap as quickly as you can.',
            'how' => [
                'Tap the big box to start. It turns red: wait.',
                'After a random pause it turns green. Tap or press Space at once.',
                'Tapping while it is still red is too early: that round is repeated.',
                'Five rounds make one test. Your average is shown at the end.',
            ],
            'scoring' => 'Your score is your average reaction time in milliseconds (ms) over five rounds: lower is better. Your best average is saved in your browser.',
            'tips' => [
                'Most people score between 200 and 300 ms on a phone or computer.',
                'Rest your finger lightly on the screen or key so you do not have to move far.',
                'Results also depend on your device and screen, so compare scores on the same device.',
            ],
        ],
        'chess' => [
            'summary' => 'Play chess against the computer at three levels, or against a friend on the same device.',
            'how' => [
                'White moves first. Tap a piece to see its legal moves (dots), then tap a square to move there.',
                'All the rules are included: castling, en passant, check, checkmate and stalemate. A pawn that reaches the last row becomes a queen.',
                'Choose vs Computer (Easy, Medium or Hard) or 2 Players to pass the device between turns.',
                'Use Undo to take back a move and Flip board to look from Black\'s side.',
            ],
            'scoring' => 'A game ends in checkmate (a win), or in a draw by stalemate, the 50-move rule or when too few pieces are left to checkmate. Your wins against each computer level are saved in your browser.',
            'tips' => [
                'Control the centre squares early with pawns and knights.',
                'Castle early to keep your king safe.',
                'Before every move, check what your opponent\'s last move attacks.',
                'Easy picks mostly random moves; Medium and Hard look two and three moves ahead.',
            ],
        ],
        'ludo' => [
            'summary' => 'The classic dice race for 2 to 4 players: play the computer or friends on one device.',
            'how' => [
                'Choose 2, 3 or 4 players, and whether the other colours are the computer or friends passing the device.',
                'Press Roll. You need a 6 to move a token out of its base onto the start square.',
                'Tap a glowing token to move it forward by the number on the dice. Tokens go once round the board, then up their own coloured lane to the centre.',
                'A 6, a capture, or bringing a token home gives you another roll. Three 6s in a row lose the turn.',
                'Landing on an opponent\'s token sends it back to its base, except on the safe squares marked with a star. You need the exact number to reach the centre.',
            ],
            'scoring' => 'The first player to bring all four tokens to the centre wins. Your wins are saved in your browser.',
            'tips' => [
                'Get several tokens out early, so every roll gives you a choice.',
                'Rest on star squares when an opponent is close behind you.',
                'When you roll a 6, think about whether a new token or a capture helps more.',
            ],
        ],
    ];

    /** Built-in games added 11-25-2026: slug => [title, audiences]. The migration inserts them In review. */
    public const NEW_GAMES = [
        'flag-challenge' => ['Flag Challenge', ['kids', 'family', 'teens', 'adults']],
        'capital-challenge' => ['Capital Challenge', ['family', 'teens', 'adults']],
        'word-search' => ['Word Search', ['kids', 'family', 'teens', 'adults']],
        'maths-race' => ['Maths Race', ['kids', 'family', 'teens']],
        'hangman' => ['Hangman', ['kids', 'family', 'teens']],
        'sudoku' => ['Sudoku', ['teens', 'adults']],
        'daily-trivia' => ['Daily Trivia', ['family', 'teens', 'adults']],
        'daily-word' => ['Daily Word', ['family', 'teens', 'adults']],
        'emoji-guess' => ['Emoji Guess', ['kids', 'family', 'teens']],
        'typing-test' => ['Typing Speed Test', ['teens', 'adults']],
        'reaction-time' => ['Reaction Time Test', ['kids', 'family', 'teens', 'adults']],
        'chess' => ['Chess', ['family', 'teens', 'adults']],
        'ludo' => ['Ludo', ['kids', 'family', 'teens', 'adults']],
    ];

    public static function for(string $slug): ?array
    {
        return self::ALL[$slug] ?? OssGames::for($slug);
    }
}
