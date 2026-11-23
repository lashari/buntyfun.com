<?php

namespace App\Content;

/**
 * Hand-written New Year 2027 wishes for /new-year-wishes.
 *
 * Stored as Message rows (category new_year). The section is read back from the slug:
 * new-year-2027-{section}-NN. Messages in new_year with any other slug (e.g. auto drafts)
 * are shown under "More New Year wishes".
 */
class NewYearWishes
{
    public const YEAR = 2027;

    public const MIN_FOR_INDEX = 10;

    /** section => [heading, short intro, language] */
    public const SECTIONS = [
        'family' => ['New Year wishes for family', 'For parents, grandparents, siblings and the family group chat.', 'en'],
        'friends' => ['New Year wishes for friends', 'For the friends who stayed, the ones far away and the ones you owe a call.', 'en'],
        'love' => ['New Year wishes for husband, wife or partner', 'Warm, simple lines for the person you are starting the year with.', 'en'],
        'work' => ['New Year wishes for colleagues, boss and clients', 'Friendly but professional — safe for email, LinkedIn or the office group.', 'en'],
        'funny' => ['Funny New Year wishes', 'Light jokes for friends and cousins. Not for your boss.', 'en'],
        'short' => ['Short New Year wishes for WhatsApp and status', 'One or two lines — good for a status, a story or a quick reply at midnight.', 'en'],
        'dua' => ['New Year wishes with a du\'a', 'Gentle wishes with a prayer, for elders and anyone who prefers a blessing to a party line.', 'en'],
        'urdu' => ['New Year wishes in Urdu (نیا سال مبارک)', 'Written in Urdu, not machine-translated. Best for parents, elders and family groups.', 'ur'],
    ];

    /** section => list of [title, body] */
    public const WISHES = [
        'family' => [
            ['For Ammi and Abbu', 'Ammi, Abbu — every good thing I will do in 2027 started with something you taught me. Happy New Year. Stay healthy, stay close, and keep the tea ready.'],
            ['For the family group', 'Happy New Year to the loudest, most loving family group on WhatsApp. May 2027 bring more dinners together and fewer "seen" messages left unanswered.'],
            ['For grandparents', 'Happy New Year, Nana and Nani. Your stories are still the best part of every visit. May this year give you good health and lots of time with all of us.'],
            ['For a brother', 'Happy New Year, bhai. Thanks for having my back in 2026 — even when you pretended not to. Let\'s make 2027 the year we finally take that trip.'],
            ['For a sister', 'Happy New Year to my sister, my first friend and my strictest critic. May 2027 give you everything you keep quietly hoping for.'],
            ['For children', 'Happy New Year to my favourite little people. Be curious, be kind, and keep asking a hundred questions a day. 2027 is yours to explore.'],
            ['For family far away', 'Distance makes New Year\'s Eve a little quieter, but you are in every countdown. Happy New Year — I hope 2027 brings us all to the same table again.'],
            ['For in-laws', 'Wishing you a very happy New Year. Thank you for welcoming me so warmly. May 2027 bring peace, good health and many happy gatherings.'],
        ],
        'friends' => [
            ['For a best friend', 'Happy New Year to the person who knows all my bad decisions of 2026 and still answers my calls. Same chaos in 2027? I\'m in.'],
            ['For an old friend', 'We don\'t talk every week, but every time we do, it feels like no time passed. Happy New Year, my friend. Let\'s meet in 2027 — for real this time.'],
            ['For a friend going through a hard year', 'I know 2026 was heavy. You got through it, and that counts. Happy New Year — I hope 2027 is gentler with you, and I\'m here whenever you need me.'],
            ['For a friend abroad', 'Happy New Year from this side of the world. Your midnight comes at a different time, but my wishes for you are the same: health, peace and good news in 2027.'],
            ['For the whole friend group', 'Happy New Year to the group chat that never sleeps. Here\'s to more plans that actually happen in 2027.'],
            ['For a new friend', 'One of the best things about 2026 was meeting you. Happy New Year — here\'s to a full year of friendship in 2027.'],
            ['For a friend chasing a goal', 'Happy New Year! This is the year your hard work starts to show. I can\'t wait to celebrate your wins in 2027.'],
            ['Thank you, friend', 'Thank you for every late-night talk, every "I\'m on my way" and every honest opinion in 2026. Happy New Year — I\'m lucky to have you.'],
        ],
        'love' => [
            ['For my wife', 'Happy New Year to my wife, my home and my favourite person to argue with about the thermostat. Every year is better because it starts with you.'],
            ['For my husband', 'Happy New Year to my husband — thank you for the small things nobody else notices. I\'m grateful for 2026 and excited for every day of 2027 with you.'],
            ['For my partner', 'Another year of you and me. Happy New Year, love — let\'s make 2027 full of slow mornings, good food and new places.'],
            ['Long distance', 'Different cities, same countdown. Happy New Year, my love. I hope 2027 is the year we close the distance.'],
            ['Simple and sweet', 'My only New Year resolution: keep choosing you. Happy New Year, jaan.'],
            ['First New Year together', 'Our first New Year together — and I already know it\'s my favourite. Happy New Year, love.'],
            ['After a tough year', 'We had hard days in 2026, but we faced them together. Happy New Year — I\'m ready for 2027 as long as it\'s with you.'],
            ['Grateful', 'If I had to pick one good thing from last year, it would be you, every time. Happy New Year.'],
        ],
        'work' => [
            ['For colleagues', 'Happy New Year, team! Thank you for the help, the patience and the jokes on busy days in 2026. Wishing you all a healthy and successful 2027.'],
            ['For a boss or manager', 'Wishing you a happy New Year. Thank you for your guidance and support during 2026 — I look forward to working together in 2027.'],
            ['For clients', 'Thank you for trusting us in 2026. Wishing you and your team a happy, healthy and successful New Year. We look forward to working with you in 2027.'],
            ['For a team you lead', 'Happy New Year to a team that made 2026 work. Rest well over the holiday — 2027 starts with all of you, and I\'m glad it does.'],
            ['For a business partner', 'Wishing you a prosperous New Year. Our work together was one of the best parts of 2026, and I look forward to building more in 2027.'],
            ['For a mentor', 'Happy New Year. Your advice made a real difference to my work in 2026. Thank you — I hope 2027 brings you as much success as you help others find.'],
            ['LinkedIn-friendly', 'Happy New Year to everyone in my network. Thank you for the conversations, the opportunities and the support in 2026. Here\'s to learning more and doing good work in 2027.'],
            ['For customers', 'Happy New Year from all of us! Thank you for choosing us in 2026. We\'re working on new things for 2027 and can\'t wait to share them with you.'],
        ],
        'funny' => [
            ['Resolutions', 'My New Year resolution is to stop making New Year resolutions. Day one: already going well. Happy New Year!'],
            ['Gym plan', 'Happy New Year! May your gym membership be used more than twice in January 2027.'],
            ['Same me', 'New year, same me — but with slightly better excuses. Happy New Year!'],
            ['Diet starts', 'Happy New Year! My diet starts tomorrow. It has started tomorrow since 2019.'],
            ['Writing the date', 'Happy New Year! Wishing you good luck writing "2027" on everything — see you in March when you finally get it right.'],
            ['Battery', 'May your 2027 be like your phone at midnight: full of messages and never on 1% battery. Happy New Year!'],
            ['Sleep', 'Happy New Year to everyone who said "I\'ll stay up till midnight" and woke up at 12:04 on the sofa.'],
            ['Group chat', 'Happy New Year! In 2027 I promise to reply to the group chat within the same week. Probably.'],
        ],
        'short' => [
            ['Short wish', 'Happy New Year! Wishing you health, peace and happiness in 2027.'],
            ['Fresh start', 'New year, fresh start. Make 2027 count. Happy New Year!'],
            ['365 chances', '365 new days, 365 new chances. Happy New Year 2027!'],
            ['Status line', 'Goodbye 2026, thank you for the lessons. Hello 2027 — let\'s go.'],
            ['Midnight message', 'It\'s midnight, and you\'re one of the first people I thought of. Happy New Year!'],
            ['Simple', 'Wishing you a year as kind as you are. Happy New Year.'],
            ['Cheers', 'Cheers to new beginnings, good people and better days. Happy New Year 2027!'],
            ['Peace', 'May 2027 bring you peace of mind and a reason to smile every day. Happy New Year.'],
        ],
        'dua' => [
            ['A prayer for health', 'As a new year begins, I pray Allah blesses you with good health, peace of heart and barakah in everything you do. Happy New Year.'],
            ['For parents', 'May Allah keep you healthy and happy in 2027, and keep your hands over our heads for many, many years. Happy New Year, Ammi and Abbu.'],
            ['For ease', 'May this new year bring ease after every difficulty and answers to the prayers you keep to yourself. Happy New Year.'],
            ['For family', 'I pray this year brings our family closer, keeps every home safe and fills every table with rizq and gratitude. Happy New Year.'],
            ['Gratitude', 'Alhamdulillah for everything 2026 gave and taught us. May 2027 bring more blessings and the patience to see them. Happy New Year.'],
            ['For a friend', 'May Allah make 2027 a year of good news for you — in your health, your work and your family. Happy New Year, my friend.'],
        ],
        'urdu' => [
            ['نیا سال مبارک', 'نیا سال مبارک ہو! اللہ کرے 2027 آپ کے لیے صحت، سکون اور ڈھیروں خوشیاں لے کر آئے۔'],
            ['امی ابو کے لیے', 'امی ابو، نیا سال مبارک! اللہ آپ کو ہمیشہ سلامت رکھے اور آپ کا سایہ ہمارے سروں پر قائم رکھے۔'],
            ['دوست کے لیے', 'یار، نیا سال مبارک! پچھلا سال تمہاری وجہ سے آسان گزرا۔ 2027 میں بھی ایسے ہی ساتھ رہنا۔'],
            ['دعا', 'اللہ کرے یہ نیا سال ہر مشکل کے بعد آسانی، ہر دعا کی قبولیت اور ہر گھر میں برکت لے کر آئے۔ نیا سال مبارک۔'],
            ['شریکِ حیات کے لیے', 'نیا سال مبارک، میری جان۔ ہر سال تمہارے ساتھ پہلے سے زیادہ خوبصورت لگتا ہے۔'],
            ['مختصر', 'نئی امیدیں، نئے خواب، نیا سال۔ آپ کو اور آپ کے گھر والوں کو نیا سال مبارک۔'],
            ['گھر والوں کے لیے', 'پورے خاندان کو نیا سال مبارک! اللہ کرے اس سال ہم سب زیادہ وقت ساتھ گزاریں۔'],
            ['مزاحیہ', 'نیا سال مبارک! اس سال کا وعدہ: ڈائٹ کل سے پکی۔ (پچھلے سال بھی یہی کہا تھا) 😄'],
            ['ساتھیوں کے لیے', 'تمام ساتھیوں کو نیا سال مبارک۔ پچھلے سال کے تعاون کا شکریہ، اللہ کرے 2027 ہم سب کے لیے کامیابی کا سال ہو۔'],
            ['شکر', 'الحمدللہ، ایک اور سال گزر گیا۔ اللہ نئے سال میں ہمیں شکر، صبر اور خوشیاں عطا کرے۔ نیا سال مبارک۔'],
        ],
    ];

    /** Flat list of rows ready for Message::create / SQL. */
    public static function rows(): array
    {
        $rows = [];
        foreach (self::WISHES as $section => $items) {
            foreach ($items as $i => [$title, $body]) {
                $rows[] = [
                    'category' => 'new_year',
                    'language' => self::SECTIONS[$section][2],
                    'title' => $title,
                    'body' => $body,
                    'slug' => self::slug($section, $i + 1),
                ];
            }
        }

        return $rows;
    }

    public static function slug(string $section, int $n): string
    {
        return sprintf('new-year-%d-%s-%02d', self::YEAR, $section, $n);
    }

    /** Section key from a slug, or null for messages not from this list. */
    public static function sectionOf(string $slug): ?string
    {
        return preg_match('/^new-year-\d{4}-([a-z]+)-\d+$/', $slug, $m) && isset(self::SECTIONS[$m[1]]) ? $m[1] : null;
    }
}
