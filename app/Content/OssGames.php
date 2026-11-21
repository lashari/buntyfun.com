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
    ];

    public static function for(string $slug): ?array
    {
        return self::ALL[$slug] ?? null;
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
