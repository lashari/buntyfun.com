<?php

namespace Database\Seeders;

use App\Models\NameEntry;
use Illuminate\Database\Seeder;

class IslamicNamesSeeder extends Seeder
{
    /**
     * Each entry below was researched against Wikipedia and specialist name-etymology
     * sites (not invented), with source_ref pointing at the specific page used. Where
     * sources genuinely disagree or the etymology is uncertain (Fatima, Ibrahim, Maryam,
     * Zainab, Khadija, Yusuf), the meaning says so rather than asserting false precision.
     *
     * Seeded as in_review, not published — this is a researched first batch, not an
     * editorially-approved one. A human with Arabic/Islamic-studies competence should
     * spot-check before publishing, per the site's own editorial policy.
     */
    public function run(): void
    {
        $names = [
            [
                'name_en' => 'Muhammad', 'name_ar' => 'محمد', 'name_ur' => 'محمد', 'gender' => 'male',
                'meaning' => 'Praiseworthy; the one who is praised. From the Arabic root ḥ-m-d, via the verb ḥammada, "to praise."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Muhammad (name)": https://en.wikipedia.org/wiki/Muhammad_(name)',
                'slug' => 'muhammad',
            ],
            [
                'name_en' => 'Ahmad', 'name_ar' => 'أحمد', 'name_ur' => 'احمد', 'gender' => 'male',
                'meaning' => 'Most praised, most commendable — an elative form from the same root (ḥ-m-d) as Muhammad and Hamid.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Ahmad": https://en.wikipedia.org/wiki/Ahmad',
                'slug' => 'ahmad',
            ],
            [
                'name_en' => 'Ali', 'name_ar' => 'علي', 'name_ur' => 'علی', 'gender' => 'male',
                'meaning' => 'Exalted, high, noble. Used in the Quran and, prefixed with "al-", forms one of the names of Allah (al-Ali).',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Ali (name)": https://en.wikipedia.org/wiki/Ali_(name)',
                'slug' => 'ali',
            ],
            [
                'name_en' => 'Omar', 'name_ar' => 'عمر', 'name_ur' => 'عمر', 'gender' => 'male',
                'meaning' => 'Flourishing, long-lived — from the Arabic root ʿ-m-r, "to flourish" or "to live long."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Omar (name)": https://en.wikipedia.org/wiki/Omar_(name)',
                'slug' => 'omar',
            ],
            [
                'name_en' => 'Hamza', 'name_ar' => 'حمزة', 'name_ur' => 'حمزہ', 'gender' => 'male',
                'meaning' => 'Lion; strong, steadfast — a traditional Arabic epithet for a lion, from a root conveying strength and fierce courage.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Hamza (name)": https://en.wikipedia.org/wiki/Hamza_(name)',
                'slug' => 'hamza',
            ],
            [
                'name_en' => 'Hassan', 'name_ar' => 'حسن', 'name_ur' => 'حسن', 'gender' => 'male',
                'meaning' => 'Handsome, good — from the Arabic root ḥ-s-n, ḥasuna, "to be beautiful, to be good."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Hassan (given name)": https://en.wikipedia.org/wiki/Hassan_(given_name)',
                'slug' => 'hassan',
            ],
            [
                'name_en' => 'Hussein', 'name_ar' => 'حسين', 'name_ur' => 'حسین', 'gender' => 'male',
                'meaning' => 'A diminutive of Hassan, from the same root (ḥ-s-n) — "little handsome one," "good," or "beautiful."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Hussein": https://en.wikipedia.org/wiki/Hussein',
                'slug' => 'hussein',
            ],
            [
                'name_en' => 'Khalid', 'name_ar' => 'خالد', 'name_ur' => 'خالد', 'gender' => 'male',
                'meaning' => 'Eternal, everlasting, immortal.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Khalid": https://en.wikipedia.org/wiki/Khalid',
                'slug' => 'khalid',
            ],
            [
                'name_en' => 'Yusuf', 'name_ar' => 'يوسف', 'name_ur' => 'یوسف', 'gender' => 'male',
                'meaning' => 'The Arabic form of the Hebrew name Joseph. Commonly glossed as "God increases" or "he will add," from a root meaning "to increase."',
                'origin' => 'Arabic, from Hebrew',
                'source_ref' => 'Quranic Names, "Yusuf": https://quranicnames.com/yusuf/',
                'slug' => 'yusuf',
            ],
            [
                'name_en' => 'Ibrahim', 'name_ar' => 'إبراهيم', 'name_ur' => 'ابراہیم', 'gender' => 'male',
                'meaning' => 'The Arabic form of Abraham. Traditionally glossed as "father of many" or "father of multitudes," though the name is ancient and non-Arabic in origin, so the precise etymology is genuinely uncertain — treat "father of many" as a traditional gloss, not a settled linguistic fact.',
                'origin' => 'Arabic, from Hebrew/older Semitic',
                'source_ref' => 'Wikipedia, "Ibrahim (name)": https://en.wikipedia.org/wiki/Ibrahim_(name); uncertainty noted by Quranic Names, "Ibrahim": https://quranicnames.com/ibrahim/',
                'slug' => 'ibrahim',
            ],
            [
                'name_en' => 'Bilal', 'name_ar' => 'بلال', 'name_ur' => 'بلال', 'gender' => 'male',
                'meaning' => 'Traditionally glossed as relating to moisture, water, or refreshment. Borne by Bilal ibn Rabah, a companion of the Prophet Muhammad and Islam\'s first muezzin.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Bilal (name)": https://en.wikipedia.org/wiki/Bilal_(name)',
                'slug' => 'bilal',
            ],
            [
                'name_en' => 'Zayd', 'name_ar' => 'زيد', 'name_ur' => 'زید', 'gender' => 'male',
                'meaning' => 'Growth, increase, abundance — from the Arabic root z-y-d, zāda, "to grow, to increase." Zaid is a common alternate spelling of the same name.',
                'origin' => 'Arabic',
                'source_ref' => 'Quranic Names, "Zayd": https://quranicnames.com/zayd/',
                'slug' => 'zayd',
            ],
            [
                'name_en' => 'Tariq', 'name_ar' => 'طارق', 'name_ur' => 'طارق', 'gender' => 'male',
                'meaning' => 'Striker, one who knocks; also used for "morning star" or "bright star" — from the verb ṭaraqa, "to strike" or "to knock."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Tariq": https://en.wikipedia.org/wiki/Tariq',
                'slug' => 'tariq',
            ],
            [
                'name_en' => 'Fatima', 'name_ar' => 'فاطمة', 'name_ur' => 'فاطمہ', 'gender' => 'female',
                'meaning' => 'The root F-Ṭ-M relates to weaning or abstaining, so the literal sense is "one who weans" or "one who abstains." The name is strongly associated with Fatima, daughter of the Prophet Muhammad — that association is traditional and historical, not itself the word\'s literal meaning. Arabic linguists are not fully certain why this root came to be used as a given name.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Fatima (given name)": https://en.wikipedia.org/wiki/Fatima_(given_name)',
                'slug' => 'fatima',
            ],
            [
                'name_en' => 'Aisha', 'name_ar' => 'عائشة', 'name_ur' => 'عائشہ', 'gender' => 'female',
                'meaning' => 'Living, alive — from the Arabic root ʿ-y-sh, meaning "to live."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Aisha (given name)": https://en.wikipedia.org/wiki/Aisha_(given_name)',
                'slug' => 'aisha',
            ],
            [
                'name_en' => 'Khadija', 'name_ar' => 'خديجة', 'name_ur' => 'خدیجہ', 'gender' => 'female',
                'meaning' => 'Traditionally glossed as "premature child" (born before term). Borne by Khadija bint Khuwaylid, the Prophet Muhammad\'s first wife.',
                'origin' => 'Arabic',
                'source_ref' => 'Behind the Name, "Khadija": https://www.behindthename.com/name/khadija',
                'slug' => 'khadija',
            ],
            [
                'name_en' => 'Zainab', 'name_ar' => 'زينب', 'name_ur' => 'زینب', 'gender' => 'female',
                'meaning' => 'Often linked to the Arabic root for beauty/grace (zayn) with the popular gloss "father\'s precious jewel" — though some sources argue that gloss is a later invention and that the name originally referred to a fragrant tree or flower. Treat the exact etymology as debated.',
                'origin' => 'Arabic',
                'source_ref' => 'Behind the Name, "Zainab": https://www.behindthename.com/name/zainab',
                'slug' => 'zainab',
            ],
            [
                'name_en' => 'Amina', 'name_ar' => 'آمنة', 'name_ur' => 'آمنہ', 'gender' => 'female',
                'meaning' => 'Two distinct Arabic names are both rendered "Amina" in English: Āmina ("safe one, protected") and Amīna ("trustworthy, honest" — the feminine form of Amin).',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Amina": https://en.wikipedia.org/wiki/Amina',
                'slug' => 'amina',
            ],
            [
                'name_en' => 'Maryam', 'name_ar' => 'مريم', 'name_ur' => 'مریم', 'gender' => 'female',
                'meaning' => 'The Arabic/Quranic form of Mary (Hebrew Miryam). The ancient etymology is genuinely debated among scholars, with proposed meanings including "beloved" and "wished-for child" — there is no single settled answer. Maryam (Mary, mother of Jesus) is named in the Quran, whose 19th surah bears her name.',
                'origin' => 'Arabic, from Hebrew/Aramaic',
                'source_ref' => 'Wikipedia, "Mariam (name)": https://en.wikipedia.org/wiki/Mariam',
                'slug' => 'maryam',
            ],
            [
                'name_en' => 'Noor', 'name_ar' => 'نور', 'name_ur' => 'نور', 'gender' => 'unisex',
                'meaning' => 'Light (physical and spiritual) — from the Arabic al-nūr.',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Noor (name)": https://en.wikipedia.org/wiki/Noor_(name)',
                'slug' => 'noor',
            ],
            [
                'name_en' => 'Layla', 'name_ar' => 'ليلى', 'name_ur' => 'لیلیٰ', 'gender' => 'female',
                'meaning' => 'Night — from the Arabic layl, "night."',
                'origin' => 'Arabic',
                'source_ref' => 'Wikipedia, "Leila (name)": https://en.wikipedia.org/wiki/Leila_(name)',
                'slug' => 'layla',
            ],
            [
                'name_en' => 'Yasmin', 'name_ar' => 'ياسمين', 'name_ur' => 'یاسمین', 'gender' => 'female',
                'meaning' => 'Jasmine flower. The Arabic word yasmin is itself usually traced back to a Persian root (yasamin) rather than being originally Arabic.',
                'origin' => 'Arabic, from Persian',
                'source_ref' => 'Arabikey, "Yasmine": https://arabikey.com/arabic-name/yasmine/',
                'slug' => 'yasmin',
            ],
            [
                'name_en' => 'Salma', 'name_ar' => 'سلمى', 'name_ur' => 'سلمیٰ', 'gender' => 'female',
                'meaning' => 'Safety, soundness, security — from the Arabic root salima, "to be safe, to be sound."',
                'origin' => 'Arabic',
                'source_ref' => 'Quranic Names, "Salma": https://quranicnames.com/salma/',
                'slug' => 'salma',
            ],
        ];

        foreach ($names as $name) {
            NameEntry::updateOrCreate(['slug' => $name['slug']], [...$name, 'status' => 'in_review']);
        }
    }
}
