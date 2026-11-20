<?php

namespace App\Content;

/**
 * Original trivia quizzes with an explanation for every answer. Facts are standard
 * reference-book facts (NASA/IAU for space, national government sources for capitals,
 * standard anatomy texts for the body). Answers: [label, is_correct].
 * Loaded by QuizLibrarySeeder (insert-only) and by the generated SQL in deploy packs.
 */
class QuizLibrary
{
    public const QUIZZES = [
        [
            'slug' => 'solar-system-quiz',
            'title' => 'Solar System Quiz: 10 Questions About Our Planets',
            'intro' => 'Ten questions about the Sun, the planets and the Moon, from easy to tricky. After you finish you will see the right answer and a short explanation for every question, so it works as a quick refresher for students and curious adults alike.',
            'questions' => [
                ['Which planet is closest to the Sun?', [['Venus', false], ['Mercury', true], ['Mars', false], ['Earth', false]],
                    'Mercury orbits closest to the Sun, at an average distance of about 58 million km. It is also the smallest of the eight planets.'],
                ['Which planet has the hottest surface?', [['Mercury', false], ['Venus', true], ['Mars', false], ['Jupiter', false]],
                    'Venus, not Mercury. Its thick carbon-dioxide atmosphere traps heat, so its surface stays around 465 °C, hot enough to melt lead.'],
                ['Which is the largest planet in the Solar System?', [['Saturn', false], ['Neptune', false], ['Jupiter', true], ['Uranus', false]],
                    'Jupiter. It has more than twice the mass of all the other planets combined.'],
                ['Roughly how long does sunlight take to reach Earth?', [['8 seconds', false], ['8 minutes', true], ['8 hours', false], ['8 days', false]],
                    'About 8 minutes and 20 seconds. Light travels about 300,000 km per second, and the Sun is about 150 million km away.'],
                ['Why is Mars called the Red Planet?', [['It is very hot', false], ['Iron oxide (rust) in its dust and rocks', true], ['Red clouds', false], ['It reflects light from the Sun', false]],
                    'Mars is covered in dust and rock rich in iron oxide, the same compound as rust, which gives it a reddish colour.'],
                ['How many planets are in the Solar System?', [['Seven', false], ['Eight', true], ['Nine', false], ['Ten', false]],
                    'Eight. Pluto was reclassified as a dwarf planet by the International Astronomical Union in 2006.'],
                ['Which planet is famous for its bright, wide rings?', [['Saturn', true], ['Mars', false], ['Venus', false], ['Mercury', false]],
                    'Saturn. Its rings are made mostly of pieces of water ice. Jupiter, Uranus and Neptune have rings too, but they are much fainter.'],
                ['What is the Sun mainly made of?', [['Oxygen and nitrogen', false], ['Hydrogen and helium', true], ['Iron and nickel', false], ['Carbon and silicon', false]],
                    'About three-quarters of the Sun\'s mass is hydrogen and about a quarter is helium. Nuclear fusion turns hydrogen into helium in its core.'],
                ['Which planet rotates almost on its side?', [['Neptune', false], ['Uranus', true], ['Saturn', false], ['Earth', false]],
                    'Uranus is tilted about 98 degrees, so it rolls around the Sun on its side. A giant collision long ago is the leading explanation.'],
                ['About how far is the Moon from Earth on average?', [['38,000 km', false], ['384,000 km', true], ['3.8 million km', false], ['150 million km', false]],
                    'About 384,400 km, roughly 30 times Earth\'s diameter. 150 million km is the distance to the Sun.'],
            ],
        ],
        [
            'slug' => 'world-capitals-quiz',
            'title' => 'World Capitals Quiz: Can You Beat the Biggest-City Trap?',
            'intro' => 'In many countries the capital is not the largest or best-known city. These ten questions test exactly that. You will see the answers and a short note about each capital at the end.',
            'questions' => [
                ['What is the capital of Australia?', [['Sydney', false], ['Melbourne', false], ['Canberra', true], ['Perth', false]],
                    'Canberra. It was chosen as a compromise between rivals Sydney and Melbourne and was built as a planned capital.'],
                ['What is the capital of Canada?', [['Toronto', false], ['Ottawa', true], ['Vancouver', false], ['Montreal', false]],
                    'Ottawa, in the province of Ontario. Toronto is Canada\'s largest city.'],
                ['What is the capital of Türkiye (Turkey)?', [['Istanbul', false], ['Ankara', true], ['Izmir', false], ['Antalya', false]],
                    'Ankara has been the capital since 1923. Istanbul is the largest city and was the Ottoman capital.'],
                ['What is the capital of Brazil?', [['Rio de Janeiro', false], ['São Paulo', false], ['Brasília', true], ['Salvador', false]],
                    'Brasília, a planned city inaugurated in 1960, replaced Rio de Janeiro as the capital.'],
                ['What is the capital of Pakistan?', [['Karachi', false], ['Lahore', false], ['Islamabad', true], ['Rawalpindi', false]],
                    'Islamabad, a planned city built in the 1960s. It replaced Karachi as the capital; nearby Rawalpindi served as the interim capital during construction.'],
                ['What is the capital of Nigeria?', [['Lagos', false], ['Abuja', true], ['Kano', false], ['Ibadan', false]],
                    'Abuja officially replaced Lagos as the capital in 1991. Lagos remains the largest city.'],
                ['Which city is the seat of the Swiss federal government?', [['Zurich', false], ['Geneva', false], ['Bern', true], ['Basel', false]],
                    'Bern. Switzerland\'s constitution does not name a capital, but Bern is the "federal city" where the government and parliament sit.'],
                ['What is the capital of Morocco?', [['Casablanca', false], ['Marrakesh', false], ['Rabat', true], ['Fez', false]],
                    'Rabat. Casablanca is the largest city and main port.'],
                ['What is the capital of Saudi Arabia?', [['Jeddah', false], ['Riyadh', true], ['Makkah', false], ['Madinah', false]],
                    'Riyadh. Jeddah is the main Red Sea port, and Makkah and Madinah are the two holiest cities in Islam.'],
                ['What is the official capital of Malaysia?', [['Kuala Lumpur', true], ['Putrajaya', false], ['Penang', false], ['Johor Bahru', false]],
                    'Kuala Lumpur is the official capital, while most government offices have moved to the nearby planned city of Putrajaya.'],
            ],
        ],
        [
            'slug' => 'human-body-quiz',
            'title' => 'Human Body Quiz: 10 Questions on How You Work',
            'intro' => 'Ten questions about bones, organs, blood and the senses. This is general-knowledge trivia, not medical advice. At the end you will see every correct answer with a one-line explanation.',
            'questions' => [
                ['How many bones does a typical adult human have?', [['106', false], ['206', true], ['306', false], ['406', false]],
                    '206. Babies are born with around 300 bones and cartilage pieces, many of which fuse together as they grow.'],
                ['What is the largest organ of the human body?', [['Liver', false], ['Brain', false], ['Skin', true], ['Lungs', false]],
                    'The skin. It covers the whole body and protects it from germs, water loss and the Sun.'],
                ['Where is the smallest bone in the body?', [['In the ear', true], ['In the nose', false], ['In the toe', false], ['In the finger', false]],
                    'The stapes (stirrup), in the middle ear. It is only about 3 mm long and helps pass sound vibrations to the inner ear.'],
                ['Which blood cells carry oxygen around the body?', [['White blood cells', false], ['Platelets', false], ['Red blood cells', true], ['Plasma cells', false]],
                    'Red blood cells. They contain haemoglobin, a protein that binds oxygen in the lungs and releases it in the tissues.'],
                ['How many chambers does the human heart have?', [['Two', false], ['Three', false], ['Four', true], ['Six', false]],
                    'Four: two atria on top that receive blood and two ventricles below that pump it out.'],
                ['Which organ produces insulin?', [['Liver', false], ['Pancreas', true], ['Kidney', false], ['Stomach', false]],
                    'The pancreas. Insulin helps cells take in sugar from the blood; problems with insulin cause diabetes.'],
                ['What is the largest internal organ?', [['Liver', true], ['Heart', false], ['Stomach', false], ['Kidney', false]],
                    'The liver. It weighs around 1.5 kg in adults and handles hundreds of jobs, from filtering blood to storing energy.'],
                ['What gas do we breathe out as a waste product?', [['Oxygen', false], ['Nitrogen', false], ['Carbon dioxide', true], ['Hydrogen', false]],
                    'Carbon dioxide. In tiny air sacs in the lungs called alveoli, oxygen passes into the blood and carbon dioxide passes out.'],
                ['Which part of the eye controls how much light gets in?', [['Cornea', false], ['Iris', true], ['Retina', false], ['Lens', false]],
                    'The iris, the coloured ring, which widens or narrows the pupil. The pupil itself is just the opening.'],
                ['What is a normal resting heart rate for most adults?', [['20-40 beats per minute', false], ['60-100 beats per minute', true], ['120-160 beats per minute', false], ['180-200 beats per minute', false]],
                    'Between 60 and 100 beats per minute is the usual range for adults at rest. Fit athletes are often lower.'],
            ],
        ],
    ];
}
