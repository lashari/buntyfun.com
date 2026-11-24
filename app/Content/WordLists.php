<?php

namespace App\Content;

/** BuntyFun's own word lists for Word Search and Hangman. Upper-case A–Z only, 3–10 letters. */
class WordLists
{
    public const WORD_SEARCH = [
        'Animals' => ['LION', 'TIGER', 'ZEBRA', 'CAMEL', 'HORSE', 'RABBIT', 'MONKEY', 'PARROT', 'EAGLE', 'SHARK', 'WHALE', 'DONKEY', 'GOAT', 'PANDA'],
        'Fruits' => ['MANGO', 'APPLE', 'BANANA', 'GUAVA', 'ORANGE', 'GRAPES', 'PEACH', 'CHERRY', 'MELON', 'LEMON', 'PAPAYA', 'LYCHEE', 'DATES', 'PLUM'],
        'Pakistan Cities' => ['LAHORE', 'KARACHI', 'QUETTA', 'MULTAN', 'SIALKOT', 'PESHAWAR', 'HYDERABAD', 'SUKKUR', 'GWADAR', 'MURREE', 'SKARDU', 'GILGIT', 'JHANG', 'KASUR'],
        'Space' => ['PLANET', 'COMET', 'GALAXY', 'ORBIT', 'ROCKET', 'MOON', 'STAR', 'SATURN', 'VENUS', 'MARS', 'NEBULA', 'METEOR', 'ECLIPSE', 'COSMOS'],
        'Sports' => ['CRICKET', 'HOCKEY', 'TENNIS', 'SQUASH', 'BOXING', 'KABADDI', 'RUGBY', 'GOLF', 'POLO', 'CHESS', 'SKIING', 'ARCHERY', 'FOOTBALL', 'JUDO'],
        'Weather' => ['RAIN', 'CLOUD', 'STORM', 'THUNDER', 'SUNNY', 'WINDY', 'SNOW', 'FOGGY', 'MONSOON', 'BREEZE', 'FROST', 'HAIL', 'RAINBOW', 'HUMID'],
        'Food' => ['BIRYANI', 'KEBAB', 'HALWA', 'SAMOSA', 'PARATHA', 'KHEER', 'LASSI', 'NIHARI', 'ROTI', 'DAAL', 'KARAHI', 'PAKORA', 'CHAI', 'PULAO'],
        'School' => ['PENCIL', 'ERASER', 'BOOK', 'TEACHER', 'DESK', 'RULER', 'LESSON', 'EXAM', 'CHALK', 'SCIENCE', 'HISTORY', 'MATHS', 'PAPER', 'CLASS'],
    ];

    /** Hangman: category => [word, hint]. */
    public const HANGMAN = [
        'Animals' => [['ELEPHANT', 'The largest land animal'], ['GIRAFFE', 'Tallest animal'], ['PENGUIN', 'A bird that swims but cannot fly'], ['CHEETAH', 'Fastest land animal'], ['DOLPHIN', 'Clever sea mammal'], ['CROCODILE', 'Reptile with a powerful bite'], ['KANGAROO', 'Hops and carries its baby in a pouch'], ['OCTOPUS', 'Eight arms'], ['SQUIRREL', 'Loves nuts'], ['BUTTERFLY', 'Starts life as a caterpillar']],
        'Countries' => [['PAKISTAN', 'Capital: Islamabad'], ['TURKEY', 'Capital: Ankara'], ['MALAYSIA', 'Home of the Petronas Towers'], ['CANADA', 'Maple leaf flag'], ['BRAZIL', 'Largest country in South America'], ['EGYPT', 'Land of the pyramids'], ['JAPAN', 'Land of the rising sun'], ['NORWAY', 'Famous for fjords'], ['MOROCCO', 'North African kingdom, capital Rabat'], ['AUSTRALIA', 'A country and a continent']],
        'Fruits & Food' => [['PINEAPPLE', 'Spiky tropical fruit'], ['WATERMELON', 'Big, green outside, red inside'], ['POMEGRANATE', 'Full of red seeds'], ['STRAWBERRY', 'Red with seeds on the outside'], ['BIRYANI', 'Spiced rice dish'], ['SAMOSA', 'Fried triangle snack'], ['CHAPATI', 'Flat bread'], ['APRICOT', 'Small orange fruit, dried in Hunza'], ['COCONUT', 'Hard shell, water inside'], ['KIWI', 'Brown fuzzy skin, green inside']],
        'Science' => [['GRAVITY', 'Keeps us on the ground'], ['OXYGEN', 'The gas we need to breathe'], ['MAGNET', 'Attracts iron'], ['VOLCANO', 'Mountain that erupts'], ['SKELETON', 'All the bones of the body'], ['PLANET', 'Earth is one'], ['ELECTRON', 'Negative particle in an atom'], ['TELESCOPE', 'Used to see stars'], ['FOSSIL', 'Remains of ancient life in rock'], ['ENERGY', 'Ability to do work']],
        'Jobs' => [['DOCTOR', 'Treats sick people'], ['TEACHER', 'Works in a school'], ['PILOT', 'Flies a plane'], ['FARMER', 'Grows crops'], ['ENGINEER', 'Designs machines and bridges'], ['CARPENTER', 'Works with wood'], ['JOURNALIST', 'Writes the news'], ['ARCHITECT', 'Designs buildings'], ['PLUMBER', 'Fixes pipes'], ['DENTIST', 'Looks after teeth']],
    ];
}
