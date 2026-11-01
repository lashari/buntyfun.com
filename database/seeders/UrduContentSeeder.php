<?php

namespace Database\Seeders;

use App\Models\BirthdayMessage;
use App\Models\Category;
use App\Models\UrduContent;
use Illuminate\Database\Seeder;

class UrduContentSeeder extends Seeder
{
    /**
     * Written directly in Urdu, not machine-translated from English content
     * elsewhere on the site. Deliberately a small, modest first batch — this
     * carries more linguistic risk than the English content seeded elsewhere,
     * so it stays narrow rather than broad.
     *
     * No poetry: real Urdu poetry is under copyright or attributed to known
     * poets, and nothing here claims to be anyone's original verse.
     *
     * Seeded as in_review, not published. This batch has NOT been checked by
     * a fluent/native Urdu speaker — do not publish on my say-so alone. The
     * "jokes" category carries the highest risk of reading flat or unnatural
     * and deserves the closest look.
     */
    public function run(): void
    {
        $items = [
            ['greetings', 'السلام علیکم! امید ہے آپ کا دن خوشیوں سے بھرا ہو۔'],
            ['greetings', 'صبح بخیر! نیا دن، نئی امید، نئی مسکراہٹ۔'],
            ['greetings', 'آپ سے مل کر طبیعت خوش ہو گئی۔ امید ہے آپ ہمیشہ خوش رہیں۔'],

            ['friendship', 'دوست وہ ہوتا ہے جو خاموشی کو بھی سمجھ لے۔'],
            ['friendship', 'سچی دوستی وقت کے ساتھ پرانی نہیں ہوتی، صرف گہری ہوتی ہے۔'],
            ['friendship', 'تمہارے بغیر ہر کہانی ادھوری لگتی ہے، میرے دوست۔'],

            ['jokes', 'استاد نے پوچھا: "تم رات کو کیوں نہیں پڑھتے؟" بچے نے کہا: "کیونکہ دن میں سونے کا وقت نہیں ملتا۔"'],
            ['jokes', 'ایک شخص نے دوسرے سے پوچھا: "تمہاری گھڑی میں وقت کیا ہے؟" دوسرے نے کہا: "وہی جو تمہاری گھڑی میں ہے، ہم دونوں اسی دنیا میں ہیں۔"'],

            ['quotes', 'وقت زخم نہیں بھرتا، وہ صرف ہمیں اُن کے ساتھ جینا سکھا دیتا ہے۔'],
            ['quotes', 'خاموشی بھی ایک زبان ہے، جسے صرف وہی سمجھتے ہیں جو دل سے سنتے ہیں۔'],
            ['quotes', 'ہر رات کے بعد صبح آتی ہے، بس صبر کا دامن مت چھوڑو۔'],

            ['inspirational', 'ہار ماننا آسان ہے، مگر کوشش جاری رکھنا ہی اصل ہمت ہے۔'],
            ['inspirational', 'منزل مشکل ہو سکتی ہے، مگر ناممکن کبھی نہیں۔'],
            ['inspirational', 'خود پر یقین رکھو، باقی راستے خود بن جاتے ہیں۔'],

            ['love', 'تمہارے ساتھ ہر لمحہ خاص لگتا ہے، چاہے وہ عام سا دن ہی کیوں نہ ہو۔'],
            ['love', 'تم سے محبت کرنا سب سے آسان کام تھا، تمہیں الفاظ میں بیان کرنا سب سے مشکل۔'],

            ['festival', 'عید مبارک! اللہ کرے یہ خوشیاں ہمیشہ آپ کے ساتھ رہیں۔'],
            ['festival', 'نیا سال آپ کی زندگی میں نئی خوشیاں اور نئی کامیابیاں لائے۔'],
        ];

        $counts = [];

        foreach ($items as [$category, $body]) {
            $counts[$category] = ($counts[$category] ?? 0) + 1;
            $number = str_pad((string) $counts[$category], 2, '0', STR_PAD_LEFT);

            UrduContent::updateOrCreate(
                ['slug' => "{$category}-{$number}"],
                ['category' => $category, 'body' => $body, 'status' => 'in_review']
            );
        }

        $this->seedUrduBirthdayWishes();
    }

    /**
     * "Birthday messages in Urdu" is its own category in the spec, but it reuses
     * birthday_messages (language='ur') rather than a separate table — the schema
     * already supports it.
     */
    private function seedUrduBirthdayWishes(): void
    {
        $category = Category::where('slug', 'birthday-wishes')->firstOrFail();

        $wishes = [
            ['best_friend', 'emotional', 'سالگرہ مبارک ہو، میرے عزیز دوست! تمہارے بغیر زندگی کا مزہ ادھورا لگتا۔'],
            ['mother', 'emotional', 'امی، سالگرہ مبارک ہو۔ آپ کی دعاؤں کی وجہ سے ہی میں آج یہاں ہوں۔'],
            ['father', 'emotional', 'ابو، سالگرہ مبارک۔ آپ نے جو سکھایا، وہی میری سب سے بڑی دولت ہے۔'],
            ['husband', 'funny', 'سالگرہ مبارک ہو! ایک سال اور بڑے ہو گئے، مگر عادتیں وہی پرانی ہیں۔'],
        ];

        foreach ($wishes as $index => [$relationship, $tone, $body]) {
            $number = str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT);
            $slugRelationship = str_replace('_', '-', $relationship);

            BirthdayMessage::updateOrCreate(
                ['slug' => "{$slugRelationship}-{$tone}-ur-{$number}"],
                [
                    'category_id' => $category->id,
                    'relationship' => $relationship,
                    'tone' => $tone,
                    'language' => 'ur',
                    'body' => $body,
                    'status' => 'in_review',
                ]
            );
        }
    }
}
