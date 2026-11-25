<?php

namespace App\Content;

/**
 * Open-source browser games served from public/play/{slug}/ inside an iframe.
 * Every entry is MIT or BSD licensed; the original LICENSE file ships in its folder
 * and the play page credits the author with a link to the source repo.
 * Keys are games.slug. Rows are inserted as in_review; an admin publishes them.
 */
class OssGames
{
    /** Audience groups shown as filters on /games. Ages, not gender. */
    public const AUDIENCES = [
        'kids' => 'Kids',
        'teens' => 'Teens',
        'adults' => 'Adults',
        'family' => 'Whole family',
    ];

    public const ALL = [
        '2048' => [
            'title' => '2048',
            'entry' => 'index.html',
            'author' => 'Gabriele Cirulli',
            'repo' => 'https://github.com/gabrielecirulli/2048',
            'license' => 'MIT',
            'genre' => 'Puzzle',
            'audience' => ['family', 'teens', 'adults'],
            'ratio' => '4/5',
            'summary' => 'Slide numbered tiles and merge them until you reach the 2048 tile.',
            'how' => [
                'Use the arrow keys, or swipe on a phone, to slide every tile in that direction.',
                'Two tiles with the same number merge into one with double the value.',
                'After each move a new 2 or 4 appears in an empty square.',
                'Reach the 2048 tile to win. The game ends when no move is left.',
            ],
            'tips' => [
                'Keep your biggest tile in one corner and build towards it.',
                'Avoid sliding in the direction that pulls the big tile out of its corner.',
            ],
        ],
        'tower-builder' => [
            'title' => 'Tower Builder',
            'entry' => 'index.html',
            'author' => 'BMQB, Inc (iamkun)',
            'repo' => 'https://github.com/iamkun/tower_game',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['kids', 'family'],
            'ratio' => '9/16',
            'summary' => 'Drop swinging blocks one on top of another and build the tallest tower.',
            'how' => [
                'A block swings from the crane at the top of the screen.',
                'Tap or click to drop it onto the tower.',
                'A block that lands on the tower scores 25 points; a perfect, straight landing scores 50.',
                'You have 3 hearts. Each block that falls off costs one heart.',
            ],
            'tips' => [
                'Wait until the block is right above the tower before you tap.',
                'Perfect drops in a row add 25 extra points each time, so take your time.',
            ],
        ],
        'classic-pool' => [
            'title' => 'Classic Pool',
            'entry' => 'index.html',
            'author' => 'Chen Shmilovich (henshmi)',
            'repo' => 'https://github.com/henshmi/Classic-Pool-Game',
            'license' => 'MIT',
            'genre' => 'Sports',
            'audience' => ['teens', 'adults'],
            'ratio' => '16/9',
            'summary' => 'Eight-ball pool against a friend or the computer, with several difficulty levels.',
            'how' => [
                'Choose to play against a friend or the computer, and pick the computer\'s level.',
                'Move the mouse to aim the cue at the white ball.',
                'Hold W to add power and S to take it away, then click to shoot.',
                'Pot all balls of your colour, then the black 8 ball, to win. Esc goes back to the menu.',
            ],
            'tips' => [
                'Softer shots are easier to control than full-power ones.',
                'Plan where the white ball will stop for your next shot.',
            ],
        ],
        'space-rocks' => [
            'title' => 'Space Rocks',
            'entry' => 'index.html',
            'author' => 'Doug McInnes',
            'repo' => 'https://github.com/dmcinnes/HTML5-Asteroids',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['family', 'teens', 'adults'],
            'ratio' => '4/3',
            'summary' => 'Fly a small ship and blast floating space rocks before they hit you.',
            'how' => [
                'Arrow left and right turn the ship, arrow up fires the thruster.',
                'Press Space to shoot. Big rocks break into smaller ones.',
                'P pauses the game and M turns the sound off.',
            ],
            'tips' => [
                'Stay near the middle and turn instead of flying around.',
                'Clear the small pieces before you break the next big rock.',
            ],
            'keyboard' => true,
        ],
        'block-drop' => [
            'title' => 'Block Drop',
            'entry' => 'index.html',
            'author' => 'Ondřej Žára',
            'repo' => 'https://github.com/ondras/custom-tetris',
            'license' => 'BSD-3-Clause',
            'genre' => 'Puzzle',
            'audience' => ['family', 'teens', 'adults'],
            'ratio' => '4/3',
            'summary' => 'Falling-block puzzle where you can also be the one who chooses the next piece.',
            'how' => [
                'First choose who picks the pieces and who places them: you or the computer.',
                'When you place pieces, the arrow keys move, turn and drop the current piece.',
                'When you pick pieces, click one or press its number key.',
                'A full row of blocks disappears. The game ends when the blocks reach the top.',
            ],
            'tips' => [
                'Keep the surface flat and leave one column open for long pieces.',
            ],
            'keyboard' => true,
        ],
        'virtual-piano' => [
            'title' => 'Virtual Piano',
            'entry' => 'index.html',
            'author' => 'Peter Coles',
            'repo' => 'https://github.com/mrcoles/javascript-piano',
            'license' => 'MIT',
            'genre' => 'Music',
            'audience' => ['kids', 'family'],
            'ratio' => '16/9',
            'summary' => 'Play a piano with your keyboard or mouse, right in the browser.',
            'how' => [
                'Click the keys with the mouse, or play with your computer keyboard.',
                'A S D F G H J K are the white keys; W E T Y U are the black keys.',
                'The arrow keys left and right move the piano one octave down or up.',
            ],
            'tips' => [
                'If you hear nothing, check that the sound of your device is on.',
            ],
            'keyboard' => true,
        ],
        'game-of-life' => [
            'title' => 'Game of Life',
            'entry' => 'index.html',
            'author' => 'Fabian Hemmer (copy)',
            'repo' => 'https://github.com/copy/life',
            'license' => 'BSD-2-Clause',
            'genre' => 'Simulation',
            'audience' => ['teens', 'adults'],
            'ratio' => '16/9',
            'summary' => 'Conway\'s Game of Life: draw cells and watch patterns grow, move and die.',
            'how' => [
                'Click cells to bring them to life, then start the simulation.',
                'A live cell with two or three live neighbours survives; others die.',
                'An empty cell with exactly three live neighbours comes to life.',
                'Load a ready-made pattern from the menu to see famous shapes.',
            ],
            'tips' => [
                'Start with a "glider": five cells that walk across the screen.',
            ],
        ],
        'particle-clicker' => [
            'title' => 'Particle Clicker',
            'entry' => 'index.html',
            'author' => 'Igor Babuschkin, Kevin Dungs, Tadej Novak, Gabor Biro',
            'repo' => 'https://github.com/particle-clicker/particle-clicker',
            'license' => 'MIT',
            'genre' => 'Clicker',
            'audience' => ['teens', 'adults'],
            'ratio' => '16/9',
            'summary' => 'Click the particle detector, collect data and grow your own physics lab.',
            'how' => [
                'Click the detector to collect data.',
                'Spend data on research to earn reputation and money.',
                'Hire workers and buy upgrades so the lab collects data for you.',
            ],
            'tips' => [
                'Hire your first workers early: they keep earning while you read.',
            ],
        ],
        'tap-tap-tap' => [
            'title' => 'TapTapTap',
            'entry' => 'play/index.html',
            'author' => 'Mahdi Al-Farra',
            'repo' => 'https://github.com/MahdiF/taptaptap',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['kids', 'family', 'teens'],
            'ratio' => '9/16',
            'summary' => 'Tap the blue circles, avoid the red ones and beat the clock, level after level.',
            'how' => [
                'Tap the circle to start.',
                'Tap every blue circle before the time bar runs out.',
                'Do not tap the red circles.',
                'Clear the level to move on; the best score is saved on your device.',
            ],
            'tips' => [
                'Look for red circles before you tap fast.',
            ],
        ],
        'the-house' => [
            'title' => 'The House',
            'entry' => 'index.html',
            'author' => 'Artur Kot',
            'repo' => 'https://github.com/arturkot/the-house-game',
            'license' => 'MIT',
            'genre' => 'Point and click',
            'audience' => ['teens', 'adults'],
            'ratio' => '16/9',
            'summary' => 'A spooky point-and-click puzzle: explore a dark old house and find the way out.',
            'how' => [
                'Click objects in the room to look at them or pick them up.',
                'Use what you find to open the way to the next room.',
                'Turn your sound on: the noises are part of the game.',
            ],
            'tips' => [
                'Click everywhere, even on dark corners.',
                'It is a very short game, so it fits in a coffee break.',
            ],
        ],
        'connect-four' => [
            'title' => 'Connect Four',
            'entry' => 'index.html',
            'author' => 'Kenrick (kenrick95)',
            'repo' => 'https://github.com/kenrick95/c4',
            'license' => 'MIT',
            'genre' => 'Board game',
            'audience' => ['kids', 'family', 'teens', 'adults'],
            'ratio' => '4/5',
            'summary' => 'Drop discs into the grid and get four in a row before your opponent, against the computer or a friend.',
            'how' => [
                'Choose a mode: against the computer (vs AI), against a friend on the same device (vs human), or online.',
                'Click or tap a column to drop your disc. It falls to the lowest free space.',
                'Players take turns. The first to line up four discs across, down or diagonally wins.',
            ],
            'tips' => [
                'The middle column is part of the most lines, so start there.',
                'Watch for your opponent building three in a row and block the fourth space.',
            ],
        ],
        'ball-and-wall' => [
            'title' => 'Ball and Wall',
            'entry' => 'index.html',
            'author' => 'Krzysztof Budnik (budnix)',
            'repo' => 'https://github.com/budnix/ball-and-wall',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['kids', 'family', 'teens'],
            'ratio' => '16/10',
            'summary' => 'Brick-breaker: bounce the ball off your paddle and smash every brick in the wall.',
            'how' => [
                'Move the paddle with the mouse, your finger, or the arrow keys left and right.',
                'Space or Enter launches the ball.',
                'Bounce the ball into the bricks to break them. Clear all bricks to finish the level.',
                'Do not let the ball fall past your paddle.',
            ],
            'tips' => [
                'Hit the ball with the edge of the paddle to send it off at a sharper angle.',
            ],
        ],
        'underrun' => [
            'title' => 'Underrun',
            'entry' => 'index.html',
            'author' => 'Dominic Szablewski (phoboslab)',
            'repo' => 'https://github.com/phoboslab/underrun',
            'license' => 'MIT',
            'genre' => 'Shooter',
            'audience' => ['teens', 'adults'],
            'ratio' => '16/9',
            'summary' => 'A dark top-down sci-fi shooter: fight robot spiders and bring the station\'s systems back online.',
            'how' => [
                'Move with W A S D or the arrow keys.',
                'Aim with the mouse and hold the mouse button to shoot.',
                'Find the terminals and follow the messages on screen to reach the next level.',
            ],
            'tips' => [
                'Keep moving: standing still makes you an easy target.',
                'Pick up health when you see it.',
            ],
            'keyboard' => true,
        ],
        'snake' => [
            'title' => 'Snake',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['kids', 'family', 'teens', 'adults'],
            'ratio' => '9/16',
            'summary' => 'Guide the snake to the food, grow longer and never bite your own tail.',
            'how' => [
                'Use the arrow keys or WASD on a computer, or swipe on a phone, to turn the snake.',
                'Eat the food to grow and score points. The snake speeds up as it grows.',
                'The game ends if the snake hits a wall or its own body.',
                'Press P or Esc to pause.',
            ],
            'tips' => [
                'Move along the edges early on to keep the middle free.',
                'Plan two turns ahead: a long snake needs room to turn round.',
            ],
        ],
        'minesweeper' => [
            'title' => 'Minesweeper',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Puzzle',
            'audience' => ['teens', 'adults', 'family'],
            'ratio' => '4/5',
            'summary' => 'Clear the board without opening a mine, using the numbers as clues.',
            'how' => [
                'Tap or click a square to open it. A number shows how many mines touch that square.',
                'Mark a square you think hides a mine with a flag (long-press on a phone, right-click on a computer).',
                'Open every safe square to win. Opening a mine ends the game.',
                'Pick an easier or harder board from the level buttons.',
            ],
            'tips' => [
                'Start with the corners and edges of open areas: numbers there give the clearest clues.',
                'A 1 touching only one closed square means that square is a mine.',
            ],
        ],
        'water-sort' => [
            'title' => 'Water Sort',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Puzzle',
            'audience' => ['kids', 'family', 'teens', 'adults'],
            'ratio' => '9/16',
            'summary' => 'Pour the coloured water between tubes until every tube holds one colour.',
            'how' => [
                'Tap a tube, then tap another tube to pour into it.',
                'You can only pour onto the same colour or into an empty tube, and only while there is room.',
                'Finish when every tube holds a single colour. Every puzzle can be solved.',
            ],
            'tips' => [
                'Use the empty tube as a parking space, not as a final home.',
                'Look for the colour that is nearly complete and finish it first.',
            ],
        ],
        'checkers' => [
            'title' => 'Checkers (Draughts)',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Board',
            'audience' => ['family', 'teens', 'adults'],
            'ratio' => '4/5',
            'summary' => 'Play draughts against the computer: jump the dark pieces and crown your kings.',
            'how' => [
                'You play the light pieces at the bottom and move first.',
                'Pieces move one square diagonally forward. Tap a piece, then a highlighted square.',
                'Jumping over an enemy piece captures it. If a jump is possible you must take it.',
                'A piece that reaches the far side becomes a king and can move backwards too.',
            ],
            'tips' => [
                'Keep your back row filled as long as you can, so the computer cannot crown kings.',
                'Trade pieces when you are ahead.',
            ],
        ],
        'ant-flap' => [
            'title' => 'Ant Flap',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Arcade',
            'audience' => ['kids', 'family', 'teens'],
            'ratio' => '9/16',
            'summary' => 'Tap to keep a tiny flying ant in the air and squeeze it between the branches.',
            'how' => [
                'Tap the screen or press Space to flap upwards.',
                'Fly through the gaps between the branches. Each gap you pass scores a point.',
                'Touching a branch or the ground ends the run.',
            ],
            'tips' => [
                'Short, steady taps work better than many fast ones.',
                'Aim for the middle of each gap.',
            ],
        ],
        'snakes-and-ladders' => [
            'title' => 'Snakes and Ladders',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Board',
            'audience' => ['kids', 'family'],
            'ratio' => '4/5',
            'summary' => 'Roll the dice, climb the ladders, dodge the snakes and reach square 100 first.',
            'how' => [
                'Press Roll to throw the dice. Your counter moves that many squares.',
                'Land at the foot of a ladder to climb up it. Land on a snake head to slide down.',
                'The first player to reach square 100 wins. No exact roll is needed to finish.',
            ],
            'tips' => [
                'It is a game of pure luck, so it is perfect for young children learning to count.',
            ],
        ],
        'reversi' => [
            'title' => 'Reversi',
            'entry' => 'index.html',
            'author' => 'mashukui',
            'repo' => 'https://github.com/mashukui/web-games',
            'license' => 'MIT',
            'genre' => 'Board',
            'audience' => ['family', 'teens', 'adults'],
            'ratio' => '1/1',
            'summary' => 'Flip the computer\'s discs to your colour: the classic Othello-style board game.',
            'how' => [
                'You play black and move first.',
                'Place a disc so that it traps a straight line of white discs between your new disc and another black one. The trapped discs flip to black.',
                'If you cannot make a move you pass. The game ends when neither side can move.',
                'Whoever has more discs at the end wins.',
            ],
            'tips' => [
                'Corners can never be flipped back, so they are the best squares on the board.',
                'Avoid the squares next to an empty corner: they hand the corner to your opponent.',
            ],
        ],
    ];

    public static function for(string $slug): ?array
    {
        return self::ALL[$slug] ?? null;
    }

    /** Card picture for any game (built-in or open-source), or null if none drawn yet. */
    public static function thumb(string $slug): ?string
    {
        $rel = 'images/games/'.$slug.'.svg';

        return is_file(public_path($rel)) ? '/'.$rel : null;
    }

    public static function src(string $slug): ?string
    {
        $g = self::for($slug);

        return $g ? '/play/'.$slug.'/'.$g['entry'] : null;
    }

    /** Audience keys for a game: admin's choice in config_json wins, then the catalog default. */
    public static function audienceOf(\App\Models\Game $game): array
    {
        $chosen = $game->config_json['audience'] ?? null;
        if (is_array($chosen)) {
            return array_values(array_intersect($chosen, array_keys(self::AUDIENCES)));
        }

        return self::for($game->slug)['audience'] ?? ['family'];
    }
}
