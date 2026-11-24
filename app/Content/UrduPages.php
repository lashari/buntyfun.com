<?php

namespace App\Content;

/**
 * Hand-written Urdu topic pages (/urdu-quotes etc.). One page = one urdu_contents.category.
 * Slug: ur-{prefix}-{section}-NN; other published rows in the category show under "مزید".
 *
 * Rules: written directly in Urdu prose, not translated; no poetry (copyright and
 * attribution risk), no lines credited to real poets or scholars, clean jokes only.
 */
class UrduPages
{
    public const MIN_FOR_INDEX = 10;

    public const PAGES = [
        'urdu-quotes' => [
            'category' => 'quotes',
            'prefix' => 'q',
            'h1' => 'اردو اقوال',
            'subtitle' => 'Urdu Quotes about Life, Patience and People',
            'title' => 'Urdu Quotes (اردو اقوال) about Life, Patience & People | BuntyFun',
            'description' => 'Original Urdu quotes about life, patience, time and people. Short lines in Urdu script to copy or send on WhatsApp and status.',
            'intro' => 'Short Urdu quotes about life, patience and the people around us, written in Urdu (not translated from English) and short enough for a WhatsApp status. These are original lines, not poetry: we don\'t copy verses or credit lines to famous poets.',
            'intro_ur' => 'زندگی، صبر اور رشتوں پر مختصر اقوال — کاپی کریں یا واٹس ایپ پر بھیجیں۔',
            'sections' => [
                'life' => ['زندگی', 'On life'],
                'patience' => ['صبر اور وقت', 'On patience and time'],
                'people' => ['لوگ اور رشتے', 'On people and relationships'],
            ],
            'items' => [
                'life' => [
                    'زندگی آسان نہیں ہوتی، اسے آسان بنانا پڑتا ہے — تھوڑا صبر، تھوڑا شکر اور تھوڑی ہنسی سے۔',
                    'جو گزر گیا اس پر افسوس کم کرو، جو سامنے ہے اس پر توجہ زیادہ۔',
                    'زندگی میں سب کچھ نہیں ملتا، مگر جو ملا ہے اس کا شکر ادا کرنا سیکھ لو تو کمی محسوس نہیں ہوتی۔',
                    'خوش رہنا ایک فیصلہ ہے جو ہر صبح خود کرنا پڑتا ہے۔',
                    'چھوٹی چھوٹی خوشیوں کو نظرانداز نہ کرو، بڑی خوشیاں انہی سے بنتی ہیں۔',
                    'ہر دن ایک نیا صفحہ ہے، کل کی غلطیاں آج کی کہانی نہیں لکھتیں۔',
                ],
                'patience' => [
                    'صبر کا پھل دیر سے ملتا ہے، مگر میٹھا ملتا ہے۔',
                    'وقت سب سے بڑا استاد ہے، مگر اس کی فیس بہت مہنگی ہے۔',
                    'جلد بازی میں کیا گیا فیصلہ اکثر لمبے پچھتاوے کی وجہ بنتا ہے۔',
                    'مشکل وقت ہمیشہ نہیں رہتا، مگر اس میں سیکھا ہوا سبق ہمیشہ ساتھ رہتا ہے۔',
                    'خاموشی سے انتظار کرنا بھی ایک ہنر ہے جو ہر کسی کو نہیں آتا۔',
                    'وقت کی قدر کرو، کیونکہ گیا ہوا وقت کبھی واپس نہیں آتا۔',
                ],
                'people' => [
                    'لوگ آپ کی باتیں بھول جاتے ہیں، مگر آپ کا رویہ یاد رکھتے ہیں۔',
                    'جو شخص آپ کی غیر موجودگی میں آپ کی عزت کرے، وہی اصل اپنا ہے۔',
                    'رشتے لفظوں سے نہیں، وقت دینے سے مضبوط ہوتے ہیں۔',
                    'ہر کسی کو خوش رکھنا ممکن نہیں، بس کسی کا دل نہ دکھاؤ۔',
                    'عزت کمانی پڑتی ہے، مانگنے سے نہیں ملتی۔',
                    'اچھے لوگ ڈھونڈنے سے زیادہ ضروری ہے خود اچھا بننا۔',
                ],
            ],
            'tips' => [
                'Urdu reads right-to-left: send the quote on its own, without English in the same message, so WhatsApp lines it up correctly.',
                'For a status, one short line works better than a long paragraph.',
                'These lines are original to BuntyFun. Please don\'t credit them to a famous poet when you share them.',
            ],
        ],

        'urdu-friendship-quotes' => [
            'category' => 'friendship',
            'prefix' => 'fr',
            'h1' => 'دوستی اردو میں',
            'subtitle' => 'Urdu Friendship Quotes and Messages',
            'title' => 'Urdu Friendship Quotes (دوستی) & Messages for Friends | BuntyFun',
            'description' => 'Original Urdu friendship quotes and messages — true friendship, missing a friend and funny lines for your dost. Copy or send on WhatsApp.',
            'intro' => 'Urdu friendship messages for your yaar, written directly in Urdu: lines about true friendship, messages for a friend far away, and a few funny ones for the group chat. Pick one, add your friend\'s name, and send it.',
            'intro_ur' => 'سچی دوستی، دور بیٹھے دوست کی یاد اور دوستوں کے لیے ہلکے پھلکے پیغامات۔',
            'sections' => [
                'true' => ['سچی دوستی', 'True friendship'],
                'missing' => ['دوست کی یاد', 'Missing a friend'],
                'funny' => ['دوستوں کے لیے مزاحیہ', 'Funny lines for friends'],
            ],
            'items' => [
                'true' => [
                    'سچا دوست وہ ہے جو آپ کی کامیابی پر آپ سے زیادہ خوش ہو۔',
                    'دوستی میں حساب نہیں رکھا جاتا، بس ساتھ نبھایا جاتا ہے۔',
                    'اچھا دوست آئینے کی طرح ہوتا ہے، سچ بتاتا ہے مگر پیار سے۔',
                    'مشکل وقت میں جو ہاتھ تھامے، وہی دوستی کا اصل مطلب سمجھتا ہے۔',
                    'دوست کم ہوں مگر سچے ہوں، یہی سب سے بڑی دولت ہے۔',
                    'دوستی وہ رشتہ ہے جو خون کا نہیں ہوتا، پھر بھی دل کا ہوتا ہے۔',
                ],
                'missing' => [
                    'فاصلے بڑھ گئے ہیں یار، مگر تمہاری یاد ویسے ہی قریب ہے۔',
                    'پرانی تصویر دیکھی تو تم یاد آ گئے۔ کب مل رہے ہو؟',
                    'وقت بدل گیا، شہر بدل گئے، مگر ہماری دوستی وہیں کی وہیں ہے۔',
                    'چائے کا کپ اور تمہاری باتیں، دونوں کی کمی محسوس ہوتی ہے۔',
                    'بہت دن ہو گئے بات کیے۔ بس یہ بتانا تھا کہ تم یاد آتے ہو۔',
                    'دوست دور ہوں تو بھی دعاؤں میں ساتھ رہتے ہیں۔',
                ],
                'funny' => [
                    'دوست وہ ہے جو تمہاری ہر غلطی جانتا ہو اور پھر بھی سب کو نہ بتائے… زیادہ تر۔',
                    'سچا دوست تمہارا آخری نوالہ بھی کھا جاتا ہے، اور پھر کہتا ہے: "یار، بھوک نہیں تھی۔"',
                    'ہمارا گروپ: منصوبے بیس، ملاقات ایک۔',
                    'دوست کو ادھار دینا ہو تو سمجھ لو تحفہ دے دیا۔ 😄',
                    'جب سب کہیں "پڑھائی کرو"، تب دوست کہتا ہے: "بس ایک چائے اور۔"',
                    'اچھا دوست وہ نہیں جو مسئلہ حل کرے، وہ ہے جو مسئلے پر ساتھ ہنسے۔',
                ],
            ],
            'tips' => [
                'Add your friend\'s name at the start — یار، یا ان کا نام — so it doesn\'t read like a forward.',
                'Funny lines are for the group chat; send the warm ones privately.',
                'If you haven\'t spoken in months, a "دوست کی یاد" line is an easy way to reconnect.',
            ],
        ],

        'urdu-love-quotes' => [
            'category' => 'love',
            'prefix' => 'lv',
            'h1' => 'محبت اردو میں',
            'subtitle' => 'Urdu Love Quotes and Messages',
            'title' => 'Urdu Love Quotes (محبت) for Husband, Wife & Partner | BuntyFun',
            'description' => 'Original Urdu love quotes and messages for your husband, wife or partner — about love, life together and missing someone. Copy or send on WhatsApp.',
            'intro' => 'Respectful Urdu love messages for a husband, wife or partner, written in Urdu prose (not borrowed poetry). There are lines about what love really is, messages for your hamsafar, and a few for when you\'re apart and missing each other.',
            'intro_ur' => 'محبت، ہمسفر کے لیے پیغامات اور یاد و انتظار کی باتیں۔',
            'sections' => [
                'love' => ['محبت', 'About love'],
                'partner' => ['ہمسفر کے لیے', 'For your husband or wife'],
                'missing' => ['یاد اور انتظار', 'Missing someone'],
            ],
            'items' => [
                'love' => [
                    'محبت لفظوں سے زیادہ چھوٹے چھوٹے خیال رکھنے میں نظر آتی ہے۔',
                    'سچی محبت وہ ہے جو عزت کے ساتھ ہو۔',
                    'محبت میں سب سے خوبصورت لمحہ وہ ہے جب کچھ کہے بغیر بات سمجھ آ جائے۔',
                    'جس کے ساتھ خاموشی بھی اچھی لگے، وہی اصل اپنا ہوتا ہے۔',
                    'محبت وقت مانگتی ہے، تحفے نہیں۔',
                    'دل کا سکون اُس کے ساتھ ہے جو تمہیں ویسے ہی قبول کرے جیسے تم ہو۔',
                ],
                'partner' => [
                    'زندگی کی ہر مشکل آسان لگتی ہے جب تم ساتھ ہو۔',
                    'شکریہ کہ تم میرے ہر اچھے برے دن میں ساتھ رہے۔',
                    'گھر وہ جگہ نہیں جہاں دیواریں ہوں، گھر وہ ہے جہاں تم ہو۔',
                    'تمہاری ایک مسکراہٹ میرے پورے دن کی تھکن اتار دیتی ہے۔',
                    'اللہ ہمارا ساتھ ہمیشہ سلامت رکھے اور ہمارے گھر کو محبت سے بھرا رکھے۔',
                    'تم میری دعا بھی ہو اور اس کی قبولیت بھی۔',
                ],
                'missing' => [
                    'تم سے دور ہوں، مگر ہر شام تمہاری یاد کے ساتھ گزرتی ہے۔',
                    'گھڑی کی سوئیاں بھی آہستہ چلتی ہیں جب تمہارا انتظار ہو۔',
                    'فون کی ہر گھنٹی پر لگتا ہے شاید تم ہو۔',
                    'فاصلہ صرف میلوں کا ہے، دلوں کا نہیں۔',
                    'جلدی واپس آ جاؤ، گھر تمہارے بغیر خالی لگتا ہے۔',
                    'ہر دعا میں تمہارا نام سب سے پہلے آتا ہے۔',
                ],
            ],
            'tips' => [
                'Keep the most personal messages private; a short line is enough for a status.',
                'Pair a line with a plan — dinner, a call tonight, the day they come home.',
                'Shared or famous poetry is often misattributed online; an original line in your own words feels more sincere.',
            ],
        ],

        'urdu-motivational-quotes' => [
            'category' => 'inspirational',
            'prefix' => 'mo',
            'h1' => 'حوصلہ افزا اقوال',
            'subtitle' => 'Urdu Motivational Quotes for Students and Hard Days',
            'title' => 'Urdu Motivational Quotes (حوصلہ) for Students & Life | BuntyFun',
            'description' => 'Original Urdu motivational quotes about hard work, exams and not giving up. Short Urdu lines for WhatsApp status or a friend who needs a push.',
            'intro' => 'Urdu motivational quotes for hard work, exams and difficult days. They are short, plain and written in Urdu, good for a WhatsApp status or for sending to a student or friend who needs encouragement.',
            'intro_ur' => 'محنت، امتحان اور مشکل وقت میں ہمت کے لیے مختصر اقوال۔',
            'sections' => [
                'work' => ['محنت اور کامیابی', 'Hard work and success'],
                'students' => ['طلبہ کے لیے', 'For students'],
                'courage' => ['مشکل وقت میں ہمت', 'Courage in hard times'],
            ],
            'items' => [
                'work' => [
                    'کامیابی ایک دن میں نہیں ملتی، مگر ایک دن ضرور ملتی ہے۔',
                    'خواب دیکھنا آسان ہے، انہیں پورا کرنے کے لیے صبح جلدی اٹھنا پڑتا ہے۔',
                    'محنت کرنے والا کبھی خالی ہاتھ نہیں لوٹتا۔',
                    'دوسروں سے مقابلہ نہیں، کل والے خود سے بہتر بنو۔',
                    'چھوٹا قدم بھی آگے کا قدم ہوتا ہے۔',
                    'جو کام آج ہو سکتا ہے، اسے کل پر مت چھوڑو۔',
                ],
                'students' => [
                    'نمبر اہم ہیں، مگر سیکھنا اس سے بھی زیادہ اہم ہے۔',
                    'ایک خراب نتیجہ تمہاری قابلیت کا فیصلہ نہیں کرتا۔ دوبارہ کوشش کرو۔',
                    'روز تھوڑا پڑھنا، امتحان سے ایک رات پہلے سب کچھ پڑھنے سے بہتر ہے۔',
                    'سوال پوچھنے سے مت ڈرو، یہی سیکھنے کا پہلا قدم ہے۔',
                    'آج کی محنت کل کی آسانی ہے۔',
                    'امتحان سے پہلے پوری نیند لو، تھکا ہوا ذہن یاد کیا ہوا بھی بھول جاتا ہے۔',
                ],
                'courage' => [
                    'ٹوٹ کر بکھرنا آسان ہے، سنبھل کر دوبارہ کھڑا ہونا اصل بہادری ہے۔',
                    'مشکل راستے اکثر خوبصورت منزلوں تک لے جاتے ہیں۔',
                    'آج اگر تھک گئے ہو تو آرام کر لو، مگر ہار مت مانو۔',
                    'لوگ کیا کہیں گے، یہ سوچ کر اپنے خواب مت چھوڑو۔',
                    'اللہ پر بھروسہ رکھو اور کوشش جاری رکھو، راستہ خود بنتا جائے گا۔',
                    '"نہیں" سننا آخر نہیں، بس اگلی کوشش کی شروعات ہے۔',
                ],
            ],
            'tips' => [
                'For someone who is struggling, add a line of your own: "میں تمہارے ساتھ ہوں".',
                'Before an exam, keep it short — a long speech adds pressure.',
                'Short lines work best as a status; send longer encouragement directly.',
            ],
        ],

        'urdu-jokes' => [
            'category' => 'jokes',
            'prefix' => 'lt',
            'h1' => 'اردو لطیفے',
            'subtitle' => 'Clean Urdu Jokes for School, Family and Friends',
            'title' => 'Urdu Jokes (اردو لطیفے) — Clean & Funny for Family | BuntyFun',
            'description' => 'Clean, family-friendly Urdu jokes about school, home and friends. Short lateefay in Urdu to copy and send on WhatsApp.',
            'intro' => 'Clean Urdu jokes (لطیفے) you can send to the family group without worrying: teacher and student jokes, jokes about home life and a few about friends. No jokes that mock religion, regions, gender or anyone\'s looks.',
            'intro_ur' => 'اسکول، گھر اور دوستوں کے صاف ستھرے لطیفے — پورے خاندان کے لیے۔',
            'sections' => [
                'school' => ['اسکول کے لطیفے', 'School jokes'],
                'home' => ['گھر کے لطیفے', 'Jokes about home'],
                'friends' => ['دوستوں کے لطیفے', 'Jokes about friends'],
            ],
            'items' => [
                'school' => [
                    'استاد: "اگر تمہارے پاس دس روپے ہوں اور تم اپنے بھائی سے دس روپے مانگو تو کتنے ہو جائیں گے؟" شاگرد: "دس ہی رہیں گے سر، آپ میرے بھائی کو نہیں جانتے۔"',
                    'استاد: "تم ہوم ورک کیوں نہیں لائے؟" شاگرد: "سر، آپ ہی نے تو کہا تھا کہ گھر کا کام گھر پر کرنا ہے۔"',
                    'استاد: "پانی کا فارمولا بتاؤ۔" شاگرد: "H I J K L M N O" استاد: "یہ کیا ہے؟" شاگرد: "سر، آپ ہی نے کہا تھا H to O۔"',
                    'استاد: "دنیا کی سب سے تیز چیز کون سی ہے؟" شاگرد: "ابو کا ہاتھ، سر — رزلٹ کارڈ دیکھتے ہی۔"',
                    'بیٹا: "ابو، میں کلاس میں سب سے آگے ہوں۔" ابو: "شاباش! کس چیز میں؟" بیٹا: "قطار میں، چھٹی کے وقت۔"',
                ],
                'home' => [
                    'بیوی: "سنیں، آج کھانے میں کیا بناؤں؟" شوہر: "جو تم کہو۔" بیوی: "تو پھر بازار سے لے آئیں۔"',
                    'امی: "کمرہ صاف کیا؟" بیٹا: "جی امی۔" امی: "پھر بستر کے نیچے یہ سب کیا ہے؟" بیٹا: "وہ کمرے کا حصہ نہیں ہے، امی۔"',
                    'مہمان: "بیٹا، بڑے ہو کر کیا بنو گے؟" بچہ: "مہمان، تاکہ روز مٹھائی ملے۔"',
                    'ابو: "تمہیں پتا ہے میں تمہاری عمر میں کتنا پڑھتا تھا؟" بیٹا: "جی ابو، دادا جان نے بتایا تھا — بالکل نہیں۔"',
                    'شوہر: "میں نے آج تک تم سے کوئی بات نہیں چھپائی۔" بیوی: "اچھا؟ تو پھر پچھلے مہینے کا بجلی کا بل کہاں ہے؟"',
                ],
                'friends' => [
                    'دوست: "یار، مجھے ادھار چاہیے۔" میں: "کتنا؟" دوست: "جتنا تم واپس مانگنا بھول جاؤ۔"',
                    'دوست: "میں نے ڈائٹ شروع کر دی ہے۔" میں: "کب سے؟" دوست: "کل سے۔" میں: "کون سے کل سے؟" دوست: "ہر کل سے۔"',
                    'دوست: "شادی کے بعد زندگی بدل جاتی ہے۔" دوسرا: "کیسے؟" دوست: "پہلے میں بولتا تھا، اب سنتا ہوں۔"',
                    'گروپ میں کوئی کہے "پانچ منٹ میں پہنچ رہا ہوں" تو سمجھ لو ابھی نہا رہا ہے۔',
                    'آدھی رات کو دوست کا میسج: "سو گئے؟" مطلب: "اٹھو، مجھے کچھ بتانا ہے۔"',
                ],
            ],
            'tips' => [
                'Send jokes one at a time — five in a row reads like spam.',
                'Keep teasing jokes for people who joke with you too.',
                'Jokes about husbands and wives are best for friends, not for in-laws.',
            ],
        ],

        'urdu-family-messages' => [
            'category' => 'family',
            'prefix' => 'fm',
            'h1' => 'گھر والوں کے لیے پیغامات',
            'subtitle' => 'Urdu Messages for Parents, Siblings and Children',
            'title' => 'Urdu Messages for Family — Parents, Siblings & Kids | BuntyFun',
            'description' => 'Original Urdu messages for parents, brothers, sisters and children — thank you, love and du\'a lines in Urdu to send on WhatsApp.',
            'intro' => 'Urdu messages for the people at home: thank-you lines and du\'as for Ammi and Abbu, messages for brothers and sisters, and words of love and advice for children. Elders often prefer Urdu, so these work well in the family group or as a message to parents living far away.',
            'intro_ur' => 'امی ابو، بہن بھائیوں اور بچوں کے لیے محبت، شکریہ اور دعا کے پیغامات۔',
            'sections' => [
                'parents' => ['والدین کے لیے', 'For parents'],
                'siblings' => ['بہن بھائی کے لیے', 'For brothers and sisters'],
                'children' => ['بچوں کے لیے', 'For children'],
            ],
            'items' => [
                'parents' => [
                    'امی ابو، آپ کی دعاؤں نے ہر مشکل راستہ آسان کیا۔ شکریہ۔',
                    'ماں کی دعا وہ سایہ ہے جو دھوپ میں بھی ساتھ رہتا ہے۔',
                    'ابو، آپ نے کبھی اپنی تھکن نہیں بتائی، مگر ہم نے ہمیشہ محسوس کی۔ شکریہ۔',
                    'امی جان، دور ہوں مگر آپ کے ہاتھ کے کھانے کی خوشبو آج بھی یاد ہے۔',
                    'اللہ ہمارے والدین کو صحت اور لمبی زندگی دے، اور ہمیں ان کی خدمت کی توفیق دے۔',
                    'جب تک ماں باپ کا ساتھ ہے، دنیا کی ہر مشکل چھوٹی لگتی ہے۔',
                ],
                'siblings' => [
                    'بھائی وہ دوست ہے جو اللہ نے خود چن کر دیا۔',
                    'بہن کی ہنسی گھر کی رونق ہے۔',
                    'بچپن کی لڑائیاں، بڑے ہو کر سب سے پیاری یادیں بن جاتی ہیں۔',
                    'بھائی، تم نے ہمیشہ میرا ساتھ دیا، چاہے میں غلط ہی کیوں نہ تھا۔ شکریہ۔',
                    'آپی، آپ نے ہمیشہ ماں کی طرح ہمارا خیال رکھا۔ اللہ آپ کو خوش رکھے۔',
                    'دور رہ کر بھی بہن بھائی کا رشتہ کبھی کمزور نہیں ہوتا۔',
                ],
                'children' => [
                    'بیٹا، ہماری ہر دعا میں تمہارا نام سب سے پہلے ہوتا ہے۔',
                    'بیٹی اللہ کی رحمت ہے، اور تم ہمارے گھر کی سب سے پیاری رحمت ہو۔',
                    'بیٹا، زندگی میں جو بھی بنو، پہلے اچھے انسان بننا۔',
                    'بیٹی، تم پر ہمیں فخر ہے۔ ہمیشہ سچ بولنا اور ہمت رکھنا۔',
                    'بچو، کامیابی سے زیادہ اہم ہے کہ تم خوش رہو اور دوسروں کو خوش رکھو۔',
                    'گھر کی رونق بچوں کی ہنسی سے ہے۔ اللہ تمہیں ہمیشہ ہنستا مسکراتا رکھے۔',
                ],
            ],
            'tips' => [
                'For parents, a short Urdu line plus a phone call means more than a long text.',
                'Send family messages in the family group on Fridays, Eid or birthdays, when everyone is reading.',
                'For children abroad or in hostels, a du\'a line is easy to send and they often keep it.',
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

    /** Urdu label for a category (falls back to the raw key). */
    public static function label(string $category): string
    {
        $url = self::forCategory($category);

        return $url ? self::PAGES[$url]['h1'] : (['greetings' => 'سلام و دعا', 'festival' => 'تہوار'][$category] ?? $category);
    }

    public static function rows(): array
    {
        $rows = [];
        foreach (self::PAGES as $page) {
            foreach ($page['items'] as $section => $items) {
                foreach ($items as $i => $body) {
                    $rows[] = ['category' => $page['category'], 'body' => $body, 'slug' => self::slug($page['prefix'], $section, $i + 1)];
                }
            }
        }

        return $rows;
    }

    public static function slug(string $prefix, string $section, int $n): string
    {
        return sprintf('ur-%s-%s-%02d', $prefix, $section, $n);
    }

    public static function sectionOf(array $page, string $slug): ?string
    {
        $p = preg_quote($page['prefix'], '/');

        return preg_match('/^ur-'.$p.'-([a-z]+)-\d+$/', $slug, $m) && isset($page['sections'][$m[1]]) ? $m[1] : null;
    }
}
