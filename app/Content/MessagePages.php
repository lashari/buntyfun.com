<?php

namespace App\Content;

/**
 * Hand-written message category pages (/good-morning-messages etc.).
 *
 * Each page = one Message category. Messages are stored as Message rows; the section is read
 * back from the slug: {prefix}-{section}-NN. Messages in the category with any other slug
 * (older rows, AI drafts) are shown under "More …".
 *
 * Text rules: original, written for a real person and occasion, no copied quotes,
 * no invented hadith or Quran references.
 */
class MessagePages
{
    public const MIN_FOR_INDEX = 10;

    /**
     * url => [
     *   category, prefix (slug), h1, title, description, intro, urdu_heading (or null),
     *   sections => [key => [heading, intro, lang]],
     *   messages => [key => [[title, body], ...]],
     *   tips => [..]
     * ]
     */
    public const PAGES = [
        'good-morning-messages' => [
            'category' => 'good_morning',
            'prefix' => 'gm',
            'h1' => 'Good Morning Messages',
            'title' => 'Good Morning Messages for Love, Friends & Family | BuntyFun',
            'description' => 'Original good morning messages for your partner, friends, family and work, plus Islamic and Urdu lines. Copy or send on WhatsApp.',
            'intro' => 'A good morning text takes five seconds to send and can set the tone for someone\'s whole day. These messages are written for a particular person: your husband or wife, a friend, your parents, the office group. Pick one, add a small detail only you would know (their exam today, last night\'s match, the weather where they are), and send it before their day gets busy.',
            'urdu_heading' => 'صبح بخیر',
            'sections' => [
                'love' => ['Good morning messages for him or her', 'For your husband, wife or partner — warm, not over the top.', 'en'],
                'friends' => ['Good morning messages for friends', 'For best friends, old friends and the group chat.', 'en'],
                'family' => ['Good morning messages for family', 'For parents, siblings and family living far away.', 'en'],
                'motivation' => ['Motivational good morning messages', 'For someone with a big day: an exam, an interview, a new job.', 'en'],
                'islamic' => ['Islamic good morning messages', 'Gentle morning greetings with a du\'a.', 'en'],
                'urdu' => ['Good morning messages in Urdu (صبح بخیر)', 'Written in Urdu for family and friends.', 'ur'],
            ],
            'messages' => [
                'love' => [
                    ['Before your coffee', 'Good morning, love. I know you\'re not fully awake until the first cup, so consider this your warm-up: you\'re loved, you\'re doing great, and tonight dinner is on me.'],
                    ['Miss you already', 'Good morning! You left for work ten minutes ago and the house already feels too quiet. Have a good day — come back soon.'],
                    ['For a long-distance partner', 'Good morning from my side of the world. Your day is just starting and mine is halfway done, but you\'re the first thing I think about in both.'],
                    ['Simple', 'Good morning, jaan. Just wanted you to start the day knowing someone is smiling because of you.'],
                    ['After a late night', 'Good morning, sleepyhead. Thank you for staying up and talking last night. Worth every yawn today.'],
                ],
                'friends' => [
                    ['Best friend', 'Good morning to the only person who gets my messages before my mother does. Go be brilliant today — and reply to me at lunch.'],
                    ['Group chat', 'Good morning, everyone! Daily reminder that this group is 90% memes and 10% actual plans. Let\'s raise that to 11% today.'],
                    ['Old friend', 'Good morning! Saw something this morning that reminded me of our college days and had to message you. Hope life is treating you well.'],
                    ['Friend having a hard week', 'Good morning. I know this week has been heavy. Take it one hour at a time — and call me if you need to talk, even for five minutes.'],
                ],
                'family' => [
                    ['For Ammi', 'Good morning, Ammi. I hope you slept well. Have your breakfast on time and don\'t worry about us — we\'re all fine.'],
                    ['For Abbu', 'Good morning, Abbu. Did you go for your walk? Take care of yourself today, and call me in the evening.'],
                    ['For a sibling', 'Good morning! Yes, this is your sibling being nice for once. Enjoy it, it won\'t last past noon.'],
                    ['Family far away', 'Good morning from here. The time zones are different but the first thing I did today was think of all of you. Love you.'],
                ],
                'motivation' => [
                    ['Exam day', 'Good morning! You\'ve prepared for this. Read every question twice, breathe, and trust what you know. Good luck today.'],
                    ['Interview', 'Good morning! Today\'s the interview. Remember: they asked to meet you because you\'re already a good fit. Just be yourself.'],
                    ['New job', 'Good morning and good luck on your first day! Nobody expects you to know everything — ask questions, learn names, and enjoy it.'],
                    ['Monday', 'Good morning! It\'s Monday, which means a whole new week to get things right. Start with one small task and the rest will follow.'],
                ],
                'islamic' => [
                    ['Assalamu alaikum', 'Assalamu alaikum, good morning. May Allah make your day easy, your work blessed and your heart at peace.'],
                    ['After Fajr', 'Good morning. May the peace of Fajr stay with you all day. Have a blessed day ahead.'],
                    ['For parents', 'Good morning, Ammi and Abbu. May Allah keep you healthy and keep your du\'as with us always.'],
                ],
                'urdu' => [
                    ['صبح بخیر', 'صبح بخیر! اللہ کرے آج کا دن آپ کے لیے خوشیوں اور آسانیوں سے بھرا ہو۔'],
                    ['امی کے لیے', 'صبح بخیر امی جان! ناشتہ وقت پر کر لیجیے گا اور اپنا خیال رکھیے گا۔'],
                    ['دوست کے لیے', 'صبح بخیر یار! چائے پی لو اور آج کا دن زبردست گزارو۔'],
                    ['دعا', 'السلام علیکم، صبح بخیر۔ اللہ آپ کا ہر کام آسان کرے اور دن بھر سکون عطا کرے۔'],
                ],
            ],
            'tips' => [
                'Send it early, before work or school starts. A good morning message at 11 AM reads like a reply, not a greeting.',
                'Add one specific line: "good luck with the presentation" or "hope your back feels better". It shows you remembered.',
                'Don\'t send the same text to everyone. Parents, partners and friends notice when a message was clearly forwarded.',
                'Urdu text reads right-to-left on WhatsApp automatically. Send it on its own, not mixed with English in the same message.',
            ],
        ],

        'good-night-messages' => [
            'category' => 'good_night',
            'prefix' => 'gn',
            'h1' => 'Good Night Messages',
            'title' => 'Good Night Messages for Love, Friends & Family | BuntyFun',
            'description' => 'Original good night messages for your partner, friends and family — sweet, funny, Islamic and Urdu. Copy or send on WhatsApp.',
            'intro' => 'A short good night message tells someone they were the last thing on your mind before you slept. These are written for real people and real evenings: a partner after a long shift, a friend who had a rough day, parents in another city. Copy one, change a word or two so it sounds like you, and send it while they\'re still awake.',
            'urdu_heading' => 'شب بخیر',
            'sections' => [
                'love' => ['Good night messages for him or her', 'For your husband, wife or partner.', 'en'],
                'friends' => ['Good night messages for friends', 'Light, kind and sometimes a little funny.', 'en'],
                'family' => ['Good night messages for family', 'For parents, children and family far away.', 'en'],
                'comfort' => ['Good night after a hard day', 'For someone who is tired, stressed or worried.', 'en'],
                'islamic' => ['Islamic good night messages', 'Calm night greetings with a du\'a.', 'en'],
                'urdu' => ['Good night messages in Urdu (شب بخیر)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'love' => [
                    ['Last thought', 'Good night, love. Of everything that happened today, talking to you was still the best part.'],
                    ['Long distance', 'Good night. It\'s strange saying it through a screen, but one day soon I\'ll say it in person every night.'],
                    ['After a long shift', 'You worked so hard today. Put the phone down, rest your eyes, and let tomorrow wait. Good night, jaan.'],
                    ['Sweet and short', 'Good night. Sleep well, and save me a spot in your dreams.'],
                    ['Thank you', 'Good night, and thank you for being patient with me today. I don\'t say it enough — I\'m lucky to have you.'],
                ],
                'friends' => [
                    ['Best friend', 'Good night! Thanks for listening to my two-hour rant today. Same time tomorrow? Kidding. Mostly.'],
                    ['Group chat', 'Good night, everyone. Whoever sends a meme after midnight is buying tea for the whole group tomorrow.'],
                    ['Friend with an early start', 'Go to sleep! You have that early meeting. Good night and good luck tomorrow.'],
                    ['Simple', 'Good night, friend. Hope you sleep well and wake up to good news.'],
                ],
                'family' => [
                    ['For Ammi and Abbu', 'Good night, Ammi and Abbu. Please don\'t stay up late watching the news. Sleep well — I\'ll call you tomorrow.'],
                    ['For children', 'Good night, my little star. Brush your teeth, say your du\'a, and dream about something fun.'],
                    ['Family far away', 'Good night from here. Different city, same moon. Miss you all.'],
                    ['For a sibling', 'Good night! Sorry for stealing your charger. I\'ll return it tomorrow. Probably.'],
                ],
                'comfort' => [
                    ['Rough day', 'Today was hard, and you still got through it. That\'s enough for one day. Rest now. Good night.'],
                    ['Worried', 'Whatever you\'re worried about will still be there in the morning, and you\'ll handle it better after some sleep. Good night.'],
                    ['Exam tomorrow', 'You\'ve studied enough. Sleep is part of the preparation now. Good night and good luck tomorrow.'],
                    ['Not feeling well', 'Drink some water, take your medicine and get proper rest. Good night — I hope you feel better in the morning.'],
                ],
                'islamic' => [
                    ['Du\'a', 'Good night. May Allah give you peaceful sleep, protect you through the night and wake you to a blessed morning.'],
                    ['Before sleep', 'Good night. Don\'t forget your night du\'as. May you sleep in Allah\'s protection.'],
                    ['For parents', 'Good night, Ammi and Abbu. May Allah keep you safe and healthy tonight and always.'],
                ],
                'urdu' => [
                    ['شب بخیر', 'شب بخیر! اللہ آپ کو پُرسکون نیند عطا کرے اور صبح خوشیوں کے ساتھ جگائے۔'],
                    ['ہمسفر کے لیے', 'شب بخیر میری جان۔ دن جیسا بھی گزرا ہو، تم سے بات کر کے سب ٹھیک لگتا ہے۔'],
                    ['دوست کے لیے', 'شب بخیر یار، اب سو جاؤ۔ کل صبح جلدی اٹھنا ہے!'],
                    ['امی ابو کے لیے', 'شب بخیر امی ابو۔ اللہ آپ کو ہمیشہ صحت مند اور سلامت رکھے۔'],
                ],
            ],
            'tips' => [
                'Keep it short at night — nobody wants to read a long paragraph when they are already half asleep.',
                'If they had a hard day, mention it gently. "Hope tomorrow is easier" means more than a generic "sweet dreams".',
                'For parents and elders, a du\'a or a simple "please rest" usually lands better than a funny line.',
                'If they have an early start, don\'t ask a question that needs a reply. Let them sleep.',
            ],
        ],

        'friendship-messages' => [
            'category' => 'friendship',
            'prefix' => 'fr',
            'h1' => 'Friendship Messages and Quotes',
            'title' => 'Friendship Messages & Quotes for Best Friends | BuntyFun',
            'description' => 'Original friendship messages for best friends, old friends and friends far away — funny, emotional and in Urdu. Copy or share on WhatsApp.',
            'intro' => 'Friends rarely tell each other what they mean to them, mostly because it feels awkward. A short message fixes that. Here you\'ll find original friendship messages for a best friend, a friend you haven\'t spoken to in months, a friend abroad, and for apologising after a fight. Copy one and add a memory you share — that\'s the part they will actually remember.',
            'urdu_heading' => 'دوستی',
            'sections' => [
                'best' => ['Messages for a best friend', 'For the person who knows everything.', 'en'],
                'distance' => ['Messages for a friend far away', 'For friends in another city or country.', 'en'],
                'reconnect' => ['Messages to reconnect with an old friend', 'For when it has been too long.', 'en'],
                'sorry' => ['Sorry messages for a friend', 'For after a fight or a misunderstanding.', 'en'],
                'funny' => ['Funny friendship messages', 'For friends who would roll their eyes at anything emotional.', 'en'],
                'urdu' => ['Friendship messages in Urdu (دوستی)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'best' => [
                    ['Thank you', 'I don\'t say it enough, so here it is: thank you for being the friend who shows up. Every time. I\'m lucky to have you.'],
                    ['Family you choose', 'You\'re not my sibling, but you\'re family. The kind I picked myself, and I\'d pick you again.'],
                    ['All the versions', 'You\'ve seen every version of me — the good, the dramatic, the "I\'m fine" one. Thanks for liking all of them.'],
                    ['Proud of you', 'Just wanted to say I\'m proud of you. I see how hard you work, even when nobody else notices.'],
                ],
                'distance' => [
                    ['Miles apart', 'Different cities, different time zones, same friendship. Miss you — let\'s video call this weekend.'],
                    ['Saw something', 'Walked past our old tea spot today and nearly called you to come down. Then I remembered you\'re a flight away. Miss you.'],
                    ['Visit soon', 'Distance is just a number until you visit. Then it\'s a countdown. Come home soon.'],
                    ['Still here', 'We don\'t talk every day anymore, but you\'re still the first person I want to tell good news to.'],
                ],
                'reconnect' => [
                    ['It has been too long', 'Hey! It\'s been far too long. No reason for this message — I just thought of you and wanted to know how you are.'],
                    ['Old memory', 'Found an old photo of us from school today. We look ridiculous. Coffee soon? I\'ll bring the photo.'],
                    ['No pressure', 'I know life got busy for both of us. No pressure to reply quickly, but I\'d really like to catch up when you\'re free.'],
                ],
                'sorry' => [
                    ['I was wrong', 'I\'ve been thinking about what happened, and I was wrong. I\'m sorry. Our friendship matters more to me than being right.'],
                    ['Can we talk', 'I don\'t like how we left things. Can we talk this week? I miss my friend.'],
                    ['Simple sorry', 'Sorry for what I said. I didn\'t mean it the way it came out. Tea on me — can we start over?'],
                ],
                'funny' => [
                    ['Secrets', 'Friendship is knowing all my secrets and still deciding not to blackmail me. Thank you for your service.'],
                    ['Group plans', 'Here\'s to us: the friends who make 20 plans a month and actually go to one.'],
                    ['Food', 'A true friend shares their food. You don\'t, but I\'ve decided to keep you anyway.'],
                    ['Bad influence', 'My mother says you\'re a bad influence. She\'s right, and that\'s why you\'re my best friend.'],
                ],
                'urdu' => [
                    ['سچی دوستی', 'سچا دوست وہی ہے جو بنا کہے حال سمجھ لے۔ شکریہ کہ تم ایسے دوست ہو۔'],
                    ['دور رہ کر بھی', 'فاصلے چاہے جتنے بھی ہوں، دوستی دل سے ہوتی ہے۔ بہت یاد آتے ہو یار۔'],
                    ['معذرت', 'یار، مجھ سے غلطی ہو گئی۔ ہماری دوستی کسی بھی بحث سے زیادہ قیمتی ہے۔ معاف کر دو۔'],
                    ['شکریہ', 'ہر مشکل وقت میں ساتھ دینے کا شکریہ۔ تم جیسا دوست قسمت والوں کو ملتا ہے۔'],
                ],
            ],
            'tips' => [
                'Mention one shared memory — a trip, a teacher, a joke only the two of you get. That is what makes it feel personal.',
                'For an old friend, don\'t start with an apology for not calling. Start with why you thought of them.',
                'Sorry messages work best when they are short and don\'t include "but". Save the explanation for the conversation.',
                'Funny messages are for friends who already joke with you. If in doubt, choose a warm one instead.',
            ],
        ],

        'motivational-quotes' => [
            'category' => 'motivational',
            'prefix' => 'mo',
            'h1' => 'Motivational Messages and Quotes',
            'title' => 'Motivational Messages & Quotes for Students and Work | BuntyFun',
            'description' => 'Original motivational messages for students, exams, work and hard days, plus Urdu lines. Short enough for WhatsApp status.',
            'intro' => 'Most motivational quotes online are copied from the same few lists and wrongly credited to famous people. These are original lines written for specific moments: before an exam, after a rejection, starting a business, or just getting through a tough week. They are short enough for a WhatsApp status and plain enough to send to a friend who needs a push.',
            'urdu_heading' => 'حوصلہ',
            'sections' => [
                'students' => ['Motivational messages for students', 'For exams, results and long study nights.', 'en'],
                'work' => ['Motivation for work and career', 'For a new job, a promotion or starting a business.', 'en'],
                'hard' => ['Messages for hard days', 'For someone who is tired, rejected or starting again.', 'en'],
                'status' => ['Short motivational lines for status', 'One line each.', 'en'],
                'urdu' => ['Motivational quotes in Urdu (حوصلہ)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'students' => [
                    ['Before the exam', 'You don\'t need to know everything. You need to stay calm and use what you know. You\'ve got this.'],
                    ['Long nights', 'Every chapter you finish tonight is one less thing to worry about tomorrow. Keep going — you\'re closer than you think.'],
                    ['After a bad result', 'One result doesn\'t decide who you are. Look at what went wrong, fix it, and try again. Plenty of successful people failed a paper.'],
                    ['Board exams', 'Sleep properly, eat something, and read the question paper slowly. Panic wastes more marks than not knowing.'],
                ],
                'work' => [
                    ['New job', 'Being new is a skill too: ask questions, take notes, and don\'t pretend to know. In three months you\'ll be the one answering.'],
                    ['Starting a business', 'Your first customers won\'t care how small you are. They\'ll care that you showed up and did good work. Start.'],
                    ['Promotion', 'You didn\'t get lucky — you earned this. Now go and make the next person\'s job easier the way someone once did for you.'],
                    ['Slow progress', 'Slow progress is still progress. Most overnight successes took about ten years.'],
                ],
                'hard' => [
                    ['Rejected', 'A "no" today is not a "no" forever. It just means not this one. Keep applying.'],
                    ['Tired', 'Rest is not giving up. Take the evening off and come back tomorrow with a clearer head.'],
                    ['Starting again', 'Starting again isn\'t starting from zero. You\'re starting from experience.'],
                    ['Comparison', 'You\'re comparing your behind-the-scenes to everyone else\'s highlight reel. Run your own race.'],
                ],
                'status' => [
                    ['Small steps', 'Small steps every day beat big plans every Monday.'],
                    ['Do it anyway', 'Scared and doing it anyway is still brave.'],
                    ['Discipline', 'Motivation gets you started. Habit keeps you going.'],
                    ['Quiet work', 'Work quietly. Let the results do the talking.'],
                ],
                'urdu' => [
                    ['ہمت', 'گر کر اٹھنا ہی اصل کامیابی ہے۔ ہمت مت ہارو۔'],
                    ['محنت', 'محنت کبھی ضائع نہیں جاتی، بس وقت لگتا ہے۔'],
                    ['امتحان', 'پرچہ آرام سے پڑھو، جو آتا ہے پہلے کرو۔ تم نے تیاری کی ہے، اللہ بہتر کرے گا۔'],
                    ['نیا آغاز', 'ہر نیا دن ایک نیا موقع ہے۔ کل کی ناکامی آج کی کوشش کو نہیں روک سکتی۔'],
                ],
            ],
            'tips' => [
                'When someone is struggling, a motivational quote alone can feel dismissive. Add "I\'m here if you want to talk."',
                'Before an exam or interview, keep it short and practical. Long speeches add pressure.',
                'Be careful with quotes "by" famous people found online — many are misattributed. Original lines avoid that problem.',
                'Short one-liners work best as a WhatsApp status; longer messages are better sent directly.',
            ],
        ],

        'thank-you-messages' => [
            'category' => 'thank_you',
            'prefix' => 'ty',
            'h1' => 'Thank You Messages',
            'title' => 'Thank You Messages for Friends, Family, Teachers & Work | BuntyFun',
            'description' => 'Original thank you messages for friends, parents, teachers, colleagues, clients and birthday wishes. Copy or send on WhatsApp or email.',
            'intro' => 'A good thank you message says what the person did and why it mattered. "Thanks for everything" is polite; "thanks for driving me to the hospital at 2 AM" is unforgettable. These messages are grouped by who you\'re thanking, from parents and teachers to clients and the people who wished you on your birthday. Fill in the detail and send it the same day if you can.',
            'urdu_heading' => 'شکریہ',
            'sections' => [
                'birthday' => ['Thank you for birthday wishes', 'For replying to everyone who wished you.', 'en'],
                'family' => ['Thank you messages for family', 'For parents, siblings and relatives.', 'en'],
                'friends' => ['Thank you messages for friends', 'For help, support and favours.', 'en'],
                'teachers' => ['Thank you messages for teachers', 'For school, college and tuition teachers.', 'en'],
                'work' => ['Thank you messages for work', 'For colleagues, bosses and clients.', 'en'],
                'urdu' => ['Thank you messages in Urdu (شکریہ)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'birthday' => [
                    ['For everyone', 'Thank you all so much for the birthday wishes! I read every single message and it made my day.'],
                    ['Group reply', 'Thank you, everyone, for the lovely wishes, calls and voice notes. Feeling very loved today.'],
                    ['For a surprise', 'Still can\'t believe you planned that surprise. Thank you for making my birthday one I\'ll never forget.'],
                    ['For a gift', 'Thank you for the gift — and for remembering that I\'d mentioned wanting it months ago. You really listen.'],
                ],
                'family' => [
                    ['For Ammi and Abbu', 'Ammi, Abbu, thank you for every sacrifice I noticed and the many I didn\'t. Everything good in my life started with you.'],
                    ['For a sibling', 'Thanks for covering for me, lending me money, and never telling Ammi. You\'re the best.'],
                    ['For hosting', 'Thank you for having us over. The food was amazing and the kids haven\'t stopped talking about it.'],
                ],
                'friends' => [
                    ['For being there', 'Thank you for picking up the phone when I needed it. You didn\'t fix anything, you just listened, and that was exactly what I needed.'],
                    ['For a favour', 'Thanks a lot for helping me move this weekend. I owe you a big dinner — your choice of place.'],
                    ['For advice', 'I took your advice and it worked. Thank you for being honest with me even when I didn\'t want to hear it.'],
                ],
                'teachers' => [
                    ['For a teacher', 'Thank you for your patience and for explaining things again when I didn\'t get them the first time. You made me like a subject I used to dread.'],
                    ['After results', 'I passed! Thank you for believing I could do it when I wasn\'t sure myself.'],
                    ['Teacher\'s Day', 'Happy Teacher\'s Day and thank you. Years from now I\'ll still remember what you taught us — not just from the book.'],
                ],
                'work' => [
                    ['For a colleague', 'Thanks for jumping in on the project last week. It wouldn\'t have been ready on time without you.'],
                    ['For a manager', 'Thank you for your support and guidance this year. I\'ve learned a lot working with you.'],
                    ['For a client', 'Thank you for your business and for the kind feedback. We really enjoyed working with you and hope to do so again.'],
                    ['After an interview', 'Thank you for taking the time to meet me today. I enjoyed learning about the role and the team, and I look forward to hearing from you.'],
                ],
                'urdu' => [
                    ['شکریہ', 'آپ کی مدد کا بہت بہت شکریہ۔ اللہ آپ کو اس کا اجر دے۔'],
                    ['سالگرہ کی مبارکباد پر', 'سالگرہ کی مبارکباد دینے کا بہت شکریہ۔ آپ سب کی دعاؤں نے دن خاص بنا دیا۔'],
                    ['امی ابو کے لیے', 'امی ابو، ہر قربانی کا شکریہ۔ آج میں جو کچھ بھی ہوں آپ کی وجہ سے ہوں۔'],
                    ['استاد کے لیے', 'محترم استاد، آپ کی محنت اور رہنمائی کا دل سے شکریہ۔'],
                ],
            ],
            'tips' => [
                'Name the specific thing they did. Specific thanks feel sincere; general thanks feel automatic.',
                'Send it soon — within a day for favours and birthday wishes, the same day for job interviews.',
                'For work, keep it to two or three sentences and skip emojis unless you already message that way.',
                'Replying to many birthday wishes? A group thank-you is fine, but send a personal line to anyone who called or sent a gift.',
            ],
        ],

        'get-well-soon-messages' => [
            'category' => 'get_well',
            'prefix' => 'gw',
            'h1' => 'Get Well Soon Messages',
            'title' => 'Get Well Soon Messages for Friends, Family & Colleagues | BuntyFun',
            'description' => 'Original get well soon messages for friends, family, colleagues and after surgery — caring, light and with a du\'a. Copy or send on WhatsApp.',
            'intro' => 'When someone is ill, they don\'t need a long message — they need to know you\'re thinking of them and won\'t expect a reply. These get well soon messages are written for different situations: a friend with the flu, a parent in hospital, a colleague after surgery. Choose the tone that fits how serious things are, and offer real help if you can, like bringing food or doing a school run.',
            'urdu_heading' => 'جلد صحت یاب ہوں',
            'sections' => [
                'friends' => ['Get well messages for friends', 'Caring, sometimes a little light.', 'en'],
                'family' => ['Get well messages for family', 'For parents, grandparents and children.', 'en'],
                'work' => ['Get well messages for colleagues and boss', 'Kind and professional.', 'en'],
                'surgery' => ['Messages after surgery or a hospital stay', 'For more serious situations. No jokes.', 'en'],
                'dua' => ['Get well messages with a du\'a', 'For Muslim friends and family.', 'en'],
                'urdu' => ['Get well messages in Urdu (جلد صحت یاب ہوں)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'friends' => [
                    ['Rest up', 'Sorry you\'re feeling rubbish. Rest, drink lots of water, and don\'t you dare reply to this. Get well soon.'],
                    ['Offer help', 'Heard you\'re unwell. Can I drop off some soup or groceries tomorrow? Just tell me what you need.'],
                    ['Light', 'Get well soon! The group chat is far too boring without your complaints.'],
                    ['Flu', 'The flu is the worst. Blankets, tea and bad TV are doctor\'s orders. Feel better soon.'],
                ],
                'family' => [
                    ['For Ammi', 'Ammi, please rest and let us do the work for a few days. Get well soon — the house doesn\'t feel right without you running it.'],
                    ['For Abbu', 'Abbu, take your medicine on time and don\'t go to the shop until the doctor says so. Get well soon.'],
                    ['For grandparents', 'Nana jaan, we\'re all praying for you. Rest well and we\'ll visit this weekend.'],
                    ['For a child', 'Get well soon, champ! Rest today and you\'ll be back on the cricket field in no time.'],
                ],
                'work' => [
                    ['Colleague', 'Sorry to hear you\'re unwell. Don\'t worry about work — we\'ve got it covered. Take the time you need and get well soon.'],
                    ['Boss', 'Wishing you a quick recovery. Everything here is on track, so please focus on getting better.'],
                    ['From the team', 'Get well soon from all of us! The office is quieter without you. Rest up and come back when you\'re ready.'],
                ],
                'surgery' => [
                    ['Before surgery', 'Thinking of you today. You\'re in good hands. I\'ll be waiting to hear that everything went well.'],
                    ['After surgery', 'So glad the operation went well. Take recovery slowly and let people help you. Wishing you strength.'],
                    ['Hospital stay', 'Sending you love while you\'re in hospital. No need to reply — just know we\'re thinking of you and here for anything.'],
                ],
                'dua' => [
                    ['Shifa', 'May Allah grant you complete shifa and make this illness a means of reward for you. Get well soon.'],
                    ['Ease', 'Praying that Allah eases your pain and returns you to full health very soon. Take care.'],
                    ['For parents', 'May Allah give you shifa, Ammi, and keep you healthy for many years. We are all praying for you.'],
                ],
                'urdu' => [
                    ['جلد صحت یاب ہوں', 'اللہ آپ کو جلد مکمل شفا عطا فرمائے۔ اپنا خیال رکھیں اور آرام کریں۔'],
                    ['دوست کے لیے', 'یار، سنا ہے طبیعت ٹھیک نہیں۔ آرام کرو، دوا وقت پر لو۔ جلدی ٹھیک ہو جاؤ۔'],
                    ['امی کے لیے', 'امی جان، اللہ آپ کو جلد صحت دے۔ کچھ دن آرام کریں، گھر کا کام ہم سنبھال لیں گے۔'],
                    ['دعا', 'اللہ تعالیٰ آپ کی تکلیف دور کرے اور آپ کو صحت و تندرستی عطا فرمائے۔ آمین۔'],
                ],
            ],
            'tips' => [
                'Say clearly that they don\'t need to reply. Ill people often feel guilty about unanswered messages.',
                'Offer something specific ("I can bring dinner on Tuesday") rather than "let me know if you need anything".',
                'Skip jokes when the illness is serious, and never share medical advice or stories about someone else who had it worse.',
                'Follow up a few days later — support often fades after the first day, but recovery takes longer.',
            ],
        ],

        'anniversary-wishes' => [
            'category' => 'anniversary',
            'prefix' => 'an',
            'h1' => 'Anniversary Wishes',
            'title' => 'Anniversary Wishes for Husband, Wife, Parents & Friends | BuntyFun',
            'description' => 'Original wedding anniversary wishes for your husband, wife, parents, friends and work anniversaries. Copy or send on WhatsApp or in a card.',
            'intro' => 'An anniversary message works best when it mentions the years you\'ve actually lived together — the first flat, the hard year, the kids, the jokes. These anniversary wishes are written for your husband or wife, your parents, a married couple you know, and for work anniversaries. Use one as a starting point and add a memory from your own years together.',
            'urdu_heading' => 'شادی کی سالگرہ مبارک',
            'sections' => [
                'spouse' => ['Anniversary wishes for husband or wife', 'For your own anniversary.', 'en'],
                'parents' => ['Anniversary wishes for parents', 'For Ammi and Abbu, or grandparents.', 'en'],
                'couple' => ['Anniversary wishes for a couple', 'For friends, siblings and relatives.', 'en'],
                'work' => ['Work anniversary wishes', 'For colleagues and team members.', 'en'],
                'urdu' => ['Anniversary wishes in Urdu', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'spouse' => [
                    ['Another year', 'Happy anniversary. Another year of your cold feet, my bad jokes and us still choosing each other. I\'d do it all again.'],
                    ['First anniversary', 'Happy first anniversary! One year of being married to my best friend, and somehow it feels like both a day and a lifetime.'],
                    ['Through the hard parts', 'Happy anniversary. We\'ve had easy years and hard ones, and you held my hand through every single one. Thank you.'],
                    ['For my wife', 'Happy anniversary to my wife — the one who makes our house a home and makes me a better man. I love you.'],
                    ['For my husband', 'Happy anniversary to my husband — my calm on busy days and my favourite person to come home to.'],
                ],
                'parents' => [
                    ['For Ammi and Abbu', 'Happy anniversary, Ammi and Abbu! Watching you two taught me what real partnership looks like. May Allah bless you with many more years together.'],
                    ['Milestone', 'Thirty years together and you still argue about the TV remote like newlyweds. Happy anniversary to the best parents in the world!'],
                    ['Grandparents', 'Happy anniversary, Dada and Dadi. Your love story is the root of this whole family.'],
                ],
                'couple' => [
                    ['For friends', 'Happy anniversary to two of my favourite people! Your marriage makes the rest of us believe in love. Have a wonderful day.'],
                    ['For a sibling', 'Happy anniversary to my sister and the brother-in-law she somehow convinced to put up with her. Love you both.'],
                    ['Card message', 'Wishing you both a very happy anniversary and many more years of laughter, patience and love.'],
                ],
                'work' => [
                    ['One year', 'Happy one-year work anniversary! It\'s been great having you on the team. Here\'s to many more.'],
                    ['Five years', 'Five years! Thank you for your hard work, your ideas and your patience with all of us. Happy work anniversary.'],
                    ['From the manager', 'Happy work anniversary. Your contribution this year has made a real difference to the team. Thank you.'],
                ],
                'urdu' => [
                    ['شادی کی سالگرہ', 'شادی کی سالگرہ مبارک ہو! اللہ آپ دونوں کا ساتھ ہمیشہ سلامت رکھے۔'],
                    ['ہمسفر کے لیے', 'ہماری شادی کی سالگرہ مبارک۔ تمہارے ساتھ ہر سال پہلے سے زیادہ خوبصورت ہوتا ہے۔'],
                    ['امی ابو کے لیے', 'امی ابو، شادی کی سالگرہ مبارک! اللہ آپ کی جوڑی کو ہمیشہ سلامت رکھے۔'],
                ],
            ],
            'tips' => [
                'Mention the number of years for milestones (10, 25, 50) — it shows you remembered.',
                'For your own spouse, one real memory beats any poetic line. "Remember our first rented flat?" says a lot.',
                'For parents, a message in the family group plus a call usually means more than a long post.',
                'Work anniversary messages should thank the person for something they did, not just congratulate them on staying.',
            ],
        ],

        'eid-mubarak-messages' => [
            'category' => 'eid',
            'prefix' => 'eid',
            'h1' => 'Eid Mubarak Messages',
            'title' => 'Eid Mubarak Messages & Wishes for Family and Friends | BuntyFun',
            'description' => 'Original Eid Mubarak wishes for Eid ul-Fitr and Eid ul-Adha — for family, friends, work and in Urdu. Copy or send on WhatsApp.',
            'intro' => 'Eid messages go out by the hundred on the morning of Eid, so a personal one stands out. These Eid Mubarak wishes cover Eid ul-Fitr after Ramadan and Eid ul-Adha, with messages for family, friends, colleagues and clients, and a section in Urdu. The traditional greeting "Taqabbal Allahu minna wa minkum" (may Allah accept from us and from you) is included where it fits.',
            'urdu_heading' => 'عید مبارک',
            'sections' => [
                'fitr' => ['Eid ul-Fitr wishes', 'For the Eid after Ramadan.', 'en'],
                'adha' => ['Eid ul-Adha wishes', 'For Bakra Eid.', 'en'],
                'family' => ['Eid wishes for family and friends', 'For parents, siblings and friends near and far.', 'en'],
                'work' => ['Eid wishes for colleagues and clients', 'Professional and respectful.', 'en'],
                'urdu' => ['Eid Mubarak messages in Urdu (عید مبارک)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'fitr' => [
                    ['After Ramadan', 'Eid Mubarak! May Allah accept your fasts, your prayers and every good deed of Ramadan. Taqabbal Allahu minna wa minkum.'],
                    ['Joy', 'Eid Mubarak! After a month of patience, may today bring you sweetness — in sheer khurma and in life.'],
                    ['Short', 'Eid Mubarak to you and your family. May this day bring peace, happiness and barakah.'],
                    ['Chand raat', 'Chand Raat Mubarak! The moon is out, the mehndi is drying and tomorrow is Eid. Eid Mubarak in advance!'],
                ],
                'adha' => [
                    ['Sacrifice', 'Eid ul-Adha Mubarak. May Allah accept your sacrifice and fill your home with blessings.'],
                    ['Sharing', 'Eid Mubarak! May the spirit of sharing this Eid reach every home, especially those who need it most.'],
                    ['Hajj', 'Eid ul-Adha Mubarak. May Allah accept the Hajj of all the pilgrims and grant us the chance to go one day.'],
                    ['Bakra Eid', 'Bakra Eid Mubarak! Wishing you a happy Eid, good food and a goat that behaves itself.'],
                ],
                'family' => [
                    ['For Ammi and Abbu', 'Eid Mubarak, Ammi and Abbu. Eid is only Eid because of you. May Allah keep you healthy and with us for many more Eids.'],
                    ['Far from home', 'Eid Mubarak from far away. Missing the family breakfast and Ammi\'s sheer khurma today. Save me some for next year!'],
                    ['For friends', 'Eid Mubarak, my friend! May this Eid bring you happiness, good food and an Eidi big enough to treat me.'],
                    ['For children', 'Eid Mubarak, little ones! Wear your new clothes, collect your Eidi and don\'t eat all the sweets before lunch.'],
                ],
                'work' => [
                    ['Colleagues', 'Eid Mubarak to you and your family. Wishing you a peaceful and joyful Eid.'],
                    ['Clients', 'Warm Eid wishes to you and your loved ones. Thank you for your trust, and may this Eid bring you peace and prosperity.'],
                    ['Team', 'Eid Mubarak, team! Enjoy the holidays with your families — you\'ve all earned the rest.'],
                ],
                'urdu' => [
                    ['عید مبارک', 'عید مبارک! اللہ آپ کی عبادتیں قبول فرمائے اور آپ کے گھر کو خوشیوں سے بھر دے۔'],
                    ['امی ابو کے لیے', 'امی ابو، عید مبارک! آپ کے بغیر عید ادھوری ہے۔ اللہ آپ کو ہمیشہ سلامت رکھے۔'],
                    ['دوست کے لیے', 'عید مبارک یار! عیدی کب دے رہے ہو؟ 😄'],
                    ['عید الاضحیٰ', 'عید الاضحیٰ مبارک! اللہ آپ کی قربانی قبول فرمائے۔'],
                    ['چاند رات', 'چاند رات مبارک! کل کی عید آپ کے لیے ڈھیروں خوشیاں لائے۔'],
                ],
            ],
            'tips' => [
                'Send family wishes after Eid prayer or the night before (Chand Raat). Networks are busy on Eid morning.',
                'For non-Muslim colleagues wishing you, a simple "Thank you! Eid Mubarak to you too" is perfect.',
                'Check which Eid it is: ul-Fitr wishes mention Ramadan and fasts, ul-Adha wishes mention sacrifice and Hajj.',
                'Urdu wishes read best when sent alone, without English in the same message.',
            ],
        ],

        'condolence-messages' => [
            'category' => 'condolence',
            'prefix' => 'co',
            'h1' => 'Condolence Messages',
            'title' => 'Condolence Messages & Sympathy Words for Loss | BuntyFun',
            'description' => 'Respectful condolence and sympathy messages for the loss of a parent, friend or colleague, including Islamic and Urdu condolences.',
            'intro' => 'Finding words after someone\'s loss is hard, and there is no perfect message. What matters is that you reach out. These condolence messages are simple and respectful, for a friend who lost a parent, a colleague, or a family you know. For Muslim families, the words "Inna lillahi wa inna ilayhi raji\'un" — "we belong to Allah and to Him we return" — are the usual first words of condolence. Keep your message short, and offer practical help if you can.',
            'urdu_heading' => 'تعزیت',
            'sections' => [
                'general' => ['Short condolence messages', 'Simple words for anyone.', 'en'],
                'parent' => ['Condolences on the loss of a parent', 'For a friend or relative who lost their mother or father.', 'en'],
                'work' => ['Condolence messages for colleagues', 'Respectful and professional.', 'en'],
                'islamic' => ['Islamic condolence messages', 'For Muslim families.', 'en'],
                'urdu' => ['Condolence messages in Urdu (تعزیت)', 'Written in Urdu.', 'ur'],
            ],
            'messages' => [
                'general' => [
                    ['Thinking of you', 'I\'m so sorry for your loss. I\'m thinking of you and your family, and I\'m here if you need anything.'],
                    ['No words', 'I don\'t have the right words, but I wanted you to know I\'m with you. Please don\'t feel you need to reply.'],
                    ['Practical help', 'I\'m very sorry. I\'d like to help — can I bring food over this week or help with anything at home?'],
                    ['Memory', 'I\'m so sorry. I\'ll always remember how kind they were to me. They will be deeply missed.'],
                ],
                'parent' => [
                    ['Loss of a mother', 'I\'m so sorry about your mother. She was a warm and generous woman, and I know how much she loved you.'],
                    ['Loss of a father', 'I\'m deeply sorry about your father. I hope the memories of him bring you some comfort in the days ahead.'],
                    ['For a close friend', 'I\'m so sorry. Losing a parent changes everything. Whatever you need — a call, a visit, or just quiet company — I\'m here.'],
                ],
                'work' => [
                    ['From a colleague', 'I was very sorry to hear about your loss. Please take all the time you need. We\'re thinking of you.'],
                    ['From the team', 'On behalf of everyone at work, please accept our heartfelt condolences. Don\'t worry about anything here.'],
                    ['To a client', 'We were saddened to hear of your loss. Please accept our sincere condolences to you and your family.'],
                ],
                'islamic' => [
                    ['Inna lillahi', 'Inna lillahi wa inna ilayhi raji\'un. I\'m so sorry for your loss. May Allah grant them Jannah and give your family patience.'],
                    ['Maghfirah', 'May Allah forgive them, have mercy on them and make their grave a place of peace. My deepest condolences.'],
                    ['Sabr', 'Inna lillahi wa inna ilayhi raji\'un. May Allah give you and your family sabr and strength at this difficult time.'],
                ],
                'urdu' => [
                    ['تعزیت', 'انا للہ وانا الیہ راجعون۔ اللہ مرحوم کی مغفرت فرمائے اور آپ کو صبر عطا کرے۔'],
                    ['والدہ کی وفات پر', 'آپ کی والدہ کی وفات کا سن کر بہت دکھ ہوا۔ اللہ ان کو جنت الفردوس میں جگہ دے۔'],
                    ['والد کی وفات پر', 'آپ کے والد صاحب کے انتقال پر دلی افسوس ہوا۔ اللہ ان کے درجات بلند فرمائے اور آپ کو صبر دے۔'],
                    ['مختصر', 'بہت افسوس ہوا۔ اللہ آپ اور آپ کے گھر والوں کو صبرِ جمیل عطا فرمائے۔'],
                ],
            ],
            'tips' => [
                'Keep it short. A grieving person reads many messages; two kind sentences are enough.',
                'Avoid "I know how you feel" or "they\'re in a better place" unless you know the family shares that view.',
                'Offer specific help — food, school pick-ups, phone calls to relatives — rather than "let me know".',
                'Check in again after a few weeks. Messages stop quickly, but grief does not.',
            ],
        ],
    ];

    public static function forCategory(string $category): ?string
    {
        foreach (self::PAGES as $url => $page) {
            if ($page['category'] === $category) {
                return $url;
            }
        }

        return null;
    }

    /** Flat list of rows ready for Message::create / SQL. */
    public static function rows(): array
    {
        $rows = [];
        foreach (self::PAGES as $page) {
            foreach ($page['messages'] as $section => $items) {
                foreach ($items as $i => [$title, $body]) {
                    $rows[] = [
                        'category' => $page['category'],
                        'language' => $page['sections'][$section][2],
                        'title' => $title,
                        'body' => $body,
                        'slug' => self::slug($page['prefix'], $section, $i + 1),
                    ];
                }
            }
        }

        return $rows;
    }

    public static function slug(string $prefix, string $section, int $n): string
    {
        return sprintf('msg-%s-%s-%02d', $prefix, $section, $n);
    }

    /** Section key from a slug for the given page, or null for messages not from this list. */
    public static function sectionOf(array $page, string $slug): ?string
    {
        $p = preg_quote($page['prefix'], '/');

        return preg_match('/^msg-'.$p.'-([a-z]+)-\d+$/', $slug, $m) && isset($page['sections'][$m[1]]) ? $m[1] : null;
    }
}
