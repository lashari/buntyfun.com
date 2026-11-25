<?php

namespace Tests\Feature;

use App\Models\AudioTrack;
use App\Models\Dua;
use App\Models\Naat;
use App\Models\NameEntry;
use App\Models\QuranAyah;
use App\Models\QuranReciter;
use App\Models\QuranSurah;
use App\Models\QuranTranslation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IslamicAudioLibraryTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    /** Page HTML with Js::from() escaping undone, to assert on the JSON handed to Alpine. */
    private function js(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();

        return preg_replace('#\\\\+/#', '/', str_replace(['\\u0022', '\\u0026'], ['"', '&'], $html));
    }

    private function admin(): User
    {
        return User::create(['name' => 'A', 'email' => 'a'.uniqid().'@test.com', 'password' => 'password', 'role' => 'admin']);
    }

    /** 114 surahs; surah 1 (7 ayahs) and 2 (3 sample ayahs) get text, plus 112 (4 ayahs). */
    private function quran(bool $publishTranslation = true): void
    {
        for ($n = 1; $n <= 114; $n++) {
            QuranSurah::create(['number' => $n, 'slug' => $n === 1 ? 'al-fatiha' : ($n === 2 ? 'al-baqara' : 's'.$n), 'name_ar' => 'سورة', 'name_en' => $n === 1 ? 'Al-Fatiha' : ($n === 2 ? 'Al-Baqara' : 'Surah '.$n),
                'name_meaning' => $n === 1 ? 'The Opening' : 'Meaning '.$n, 'revelation' => 'meccan', 'ayah_count' => $n === 1 ? 7 : 3]);
        }
        $g = 0;
        foreach ([[1, 7], [2, 3]] as [$s, $count]) {
            for ($a = 1; $a <= $count; $a++) {
                $g = $s === 1 ? $a : 7 + $a;
                $text = $s === 2 && $a === 1 ? 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ الٓمٓ' : "آيَةٌ {$s}-{$a} ٱلرَّحْمَٰنِ";
                QuranAyah::create(['id' => $g, 'surah' => $s, 'ayah' => $a, 'juz' => 1, 'text_uthmani' => $text, 'text_simple' => \App\Http\Controllers\QuranController::normalizeArabic($text)]);
            }
        }
        foreach ([1, 2, 3, 4] as $a) {
            QuranAyah::create(['id' => 6221 + $a, 'surah' => 112, 'ayah' => $a, 'juz' => 30, 'text_uthmani' => "اخلاص {$a}", 'text_simple' => "اخلاص {$a}"]);
        }
        $t = QuranTranslation::create(['code' => 'en.pickthall', 'language' => 'en', 'name' => 'English — Pickthall', 'translator' => 'Pickthall', 'license' => 'Public domain', 'status' => $publishTranslation ? 'published' : 'in_review']);
        DB::table('quran_translation_texts')->insert(['translation_id' => $t->id, 'ayah_id' => 1, 'text' => 'In the name of Allah, the Beneficent, the Merciful.']);
        QuranReciter::create(['slug' => 'husary', 'name' => 'Mahmoud Khalil Al-Husary', 'country' => 'Egypt', 'style' => 'murattal',
            'url_pattern' => 'https://cdn.islamic.network/quran/audio/128/ar.husary/{g}.mp3', 'source' => 'Islamic Network', 'license_note' => 'test', 'status' => 'published']);
        QuranReciter::create(['slug' => 'hidden', 'name' => 'Not Yet Approved', 'style' => 'murattal',
            'url_pattern' => 'https://everyayah.com/data/x/{sss}{aaa}.mp3', 'source' => 'x', 'license_note' => 'x', 'status' => 'in_review']);
    }

    public function test_quran_index_and_surah_page_with_audio_translation_and_canonical_url(): void
    {
        $this->quran();

        $this->get(route('quran.index'))->assertOk()->assertSee('Al-Fatiha')->assertSee('Mahmoud Khalil Al-Husary')->assertDontSee('Not Yet Approved');
        $this->get('/quran/1')->assertRedirect('/quran/1-al-fatiha')->assertStatus(301);
        $this->get('/quran/1-wrong')->assertRedirect('/quran/1-al-fatiha');
        $this->get('/quran/115')->assertNotFound();

        $html = $this->get('/quran/1-al-fatiha')->assertOk()
            ->assertSee('In the name of Allah, the Beneficent, the Merciful.')
            ->assertSee('id="ayah-7"', false)
            ->assertSee('aria-label="Play verse 3"', false)
            ->assertSee('Tanzil Project')
            ->assertSee('role="region" aria-label="Audio player"', false)
            ->getContent();
        $this->assertStringNotContainsString('Not Yet Approved', $html);
        $html = $this->js('/quran/1-al-fatiha');
        $this->assertStringContainsString('ar.husary/{g}.mp3', $html);
        $this->assertStringContainsString('"basmala":false', $html); // Fatiha: basmala is verse 1, no extra header
    }

    public function test_basmala_is_shown_as_header_not_repeated_in_verse_one(): void
    {
        $this->quran();
        $html = $this->js('/quran/2-al-baqara');
        $this->assertStringContainsString('"basmala":true', $html);
        $this->assertSame(1, substr_count($html, 'بِسْمِ ٱللَّهِ ٱلرَّحْمَٰنِ ٱلرَّحِيمِ'));
        $this->assertStringContainsString('الٓمٓ', $html);
    }

    public function test_unpublished_translation_is_not_shown(): void
    {
        $this->quran(false);
        $this->get('/quran/1-al-fatiha')->assertOk()->assertDontSee('In the name of Allah, the Beneficent');
    }

    public function test_quran_pages_404_until_text_is_imported(): void
    {
        $this->get(route('quran.index'))->assertNotFound();
        $this->get('/quran/1-al-fatiha')->assertNotFound();
    }

    public function test_quran_search_arabic_ignores_diacritics_and_english_uses_published_translation(): void
    {
        $this->quran();
        $this->get(route('quran.search', ['q' => 'الرحمن']))->assertOk()->assertSee('Al-Fatiha 1:1');
        $this->get(route('quran.search', ['q' => 'Beneficent']))->assertOk()->assertSee('1 verse');
        $this->get(route('quran.search', ['q' => 'opening']))->assertOk()->assertSee('1. Al-Fatiha');
        $this->get(route('quran.search', ['q' => '%']))->assertOk();
    }

    public function test_quranic_dua_listen_plays_recitation_of_its_verses_and_hadith_dua_uses_voice(): void
    {
        $this->quran();
        $q = Dua::create(['slug' => 'ikhlas', 'title' => 'Surah Ikhlas', 'category' => 'protection', 'arabic' => 'قُلْ هُوَ ٱللَّهُ أَحَدٌ', 'translation_en' => 'Say: He is Allah, the One.',
            'reference' => 'Quran 112:1-4; Sunan Abi Dawud 5082', 'grading' => 'Quran', 'status' => 'published']);
        $h = Dua::create(['slug' => 'after-adhan', 'title' => 'Dua after the adhan', 'category' => 'prayer', 'arabic' => 'اللَّهُمَّ رَبَّ هَذِهِ الدَّعْوَةِ التَّامَّةِ', 'translation_en' => 'O Allah, Lord of this perfect call.',
            'reference' => 'Sahih al-Bukhari 614', 'grading' => 'Sahih', 'grading_by' => 'In Sahih al-Bukhari', 'status' => 'published']);

        $this->assertSame([[112, 1], [112, 2], [112, 3], [112, 4]], $q->quranVerses());
        $this->assertSame([], $h->quranVerses());
        $this->assertSame([[2, 255]], (new Dua(['reference' => 'Quran 2:255; Sahih al-Bukhari 2311']))->quranVerses());
        $this->assertSame([[112, 1], [113, 1], [113, 2]], (new Dua(['reference' => 'Quran 112:1, 113:1-2']))->quranVerses());

        $this->get(route('duas.show', $q))->assertOk()->assertSee('Quran recitation');
        $html = $this->js(route('duas.show', $q));
        $this->assertStringContainsString('"type":"quran"', $html);
        $this->assertStringContainsString('"g":6222', $html);
        $this->assertStringContainsString('ar.husary/{g}.mp3', $html);

        $this->get(route('duas.show', $h))->assertOk()->assertSee('Device voice (automatic, not a recitation)')->assertSee('Grading:')->assertSee('Sahih');
        $this->get(route('duas.index'))->assertOk()->assertSee('Listen to Surah Ikhlas', false)->assertSee('Listen to Dua after the adhan', false);
    }

    public function test_dua_with_reviewed_recording_plays_the_file(): void
    {
        Storage::fake('public');
        $d = Dua::create(['slug' => 'x', 'title' => 'X dua', 'category' => 'daily', 'arabic' => 'غُفْرَانَكَ', 'translation_en' => 'I ask Your forgiveness.', 'reference' => 'Sunan Abi Dawud 30', 'status' => 'published']);
        AudioTrack::create(['kind' => 'dua', 'ref_id' => $d->id, 'file_path' => 'audio/dua/x.mp3', 'speaker' => 'Qari Test', 'license' => 'Own recording', 'status' => 'published']);
        $this->get(route('duas.show', $d))->assertOk()->assertSee('Recorded by Qari Test');
        $this->assertStringContainsString('audio/dua/x.mp3', $this->js(route('duas.show', $d)));
    }

    public function test_dua_lists_never_shorten_arabic_and_category_pages_work(): void
    {
        $long = str_repeat('اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَافِيَةَ ', 12).'آخِرُ';
        foreach (['a' => 'sleep', 'b' => 'sleep', 'c' => 'travel'] as $slug => $cat) {
            Dua::create(['slug' => $slug, 'title' => 'Dua '.$slug, 'category' => $cat, 'arabic' => $long, 'translation_en' => 'Meaning', 'reference' => 'Sahih al-Bukhari 1', 'status' => 'published']);
        }
        $this->get(route('duas.index'))->assertOk()->assertSee('آخِرُ')->assertSee('Sleep <span class="opacity-70">(2)</span>', false);
        $this->get(route('duas.category', 'sleep'))->assertOk()->assertSee('Dua a')->assertSee('Dua b')->assertDontSee('>Dua c<', false)->assertSee('آخِرُ');
        $this->get(route('duas.category', 'illness'))->assertNotFound();
        $this->get(route('duas.category', 'nope'))->assertNotFound();
        $this->get(route('sitemap'))->assertOk()->assertSee(route('duas.category', 'sleep'))->assertDontSee(route('duas.category', 'travel'));
    }

    public function test_name_play_button_uses_recording_when_published_else_device_voice_and_flags_uncertain(): void
    {
        $n = NameEntry::create(['name_en' => 'Amin', 'name_ar' => 'أمين', 'name_ur' => 'امین', 'pronunciation' => 'ah-MEEN', 'gender' => 'male', 'meaning' => 'Trustworthy.',
            'origin' => 'Arabic', 'category' => 'quranic', 'source_ref' => 'Quran 26:107: https://quran.com/26/107', 'verification' => 'source_checked', 'slug' => 'amin', 'status' => 'published']);
        $t = NameEntry::create(['name_en' => 'Taha', 'name_ar' => 'طه', 'gender' => 'male', 'meaning' => 'Opening letters; meaning known only to Allah.',
            'origin' => 'Arabic', 'category' => 'quranic', 'source_ref' => 'Quran 20:1: https://quran.com/20/1', 'verification' => 'uncertain', 'slug' => 'taha', 'status' => 'published']);

        $this->get(route('name-entries.show', $n))->assertOk()->assertSee('Hear how to pronounce Amin', false)->assertSee('ah-MEEN')->assertSee('Device voice (automatic)')->assertDontSee('meaning of this name is uncertain');
        $this->get(route('name-entries.show', $t))->assertOk()->assertSee('The meaning of this name is uncertain');
        $this->get(route('name-entries.list', 'boys'))->assertOk()->assertSee('Hear how to pronounce Amin', false);

        AudioTrack::create(['kind' => 'name', 'ref_id' => $n->id, 'external_url' => 'https://example.org/amin.mp3', 'license' => 'CC BY 4.0', 'status' => 'published']);
        $this->get(route('name-entries.show', $n))->assertOk()->assertSee('Recorded pronunciation');
        $this->assertStringContainsString('https://example.org/amin.mp3', $this->js(route('name-entries.show', $n)));
        $this->get(route('name-entries.index', ['q' => 'امین']))->assertOk()->assertSee('Amin');
    }

    public function test_naats_only_published_with_category_pages_and_audio_schema(): void
    {
        $n = Naat::create(['slug' => 'test-naat', 'title' => 'Test Naat', 'title_ur' => 'نعت', 'category' => 'urdu', 'poet' => 'Poet X', 'reciter' => 'Reciter Y', 'license' => 'Permission letter', 'status' => 'published']);
        Naat::create(['slug' => 'hidden-naat', 'title' => 'Hidden Naat', 'category' => 'urdu', 'status' => 'in_review']);
        AudioTrack::create(['kind' => 'naat', 'ref_id' => $n->id, 'external_url' => 'https://example.org/naat.mp3', 'speaker' => 'Reciter Y', 'license' => 'Permission letter', 'status' => 'published']);

        $this->get(route('naats.index'))->assertOk()->assertSee('Test Naat')->assertDontSee('Hidden Naat')->assertSee('Play Test Naat', false);
        $this->get(route('naats.category', 'urdu'))->assertOk()->assertSee('Test Naat');
        $this->get(route('naats.category', 'bad'))->assertNotFound();
        $this->get(route('naats.show', 'hidden-naat'))->assertNotFound();
        $this->get(route('naats.show', $n))->assertOk()->assertSee('"@type":"AudioObject"', false)->assertSee('Poet X')->assertSee('Reciter Y');
        $this->get(route('naats.index', ['q' => 'Poet X']))->assertOk()->assertSee('Test Naat')->assertSee('noindex', false);
        $this->get(route('search', ['q' => 'Test Naat']))->assertOk()->assertSee(route('naats.show', $n));
    }

    public function test_sitemap_and_nav_include_quran_once_imported(): void
    {
        $this->get(route('sitemap'))->assertOk()->assertDontSee(route('quran.index'));
        // full import needed for sitemap: 6236 ayahs
        $this->quran();
        $this->get(route('sitemap'))->assertOk()->assertDontSee('/quran/1-al-fatiha'); // only 14 ayahs in test data
        Cache::flush();
        $this->get(route('home'))->assertOk()->assertSee(route('quran.index'));
    }

    public function test_admin_can_manage_reciters_translations_naats_and_upload_audio_with_licence(): void
    {
        Storage::fake('public');
        $this->quran();
        $admin = $this->admin();
        NameEntry::create(['name_en' => 'Amin', 'name_ar' => 'أمين', 'gender' => 'male', 'meaning' => 'Trustworthy.', 'source_ref' => 'Quran 26:107', 'slug' => 'amin', 'status' => 'published']);

        foreach (['reciters', 'translations', 'naats', 'audio'] as $type) {
            $this->actingAs($admin)->get(route('admin.library.index', $type))->assertOk();
            $this->actingAs($admin)->get(route('admin.library.create', $type))->assertOk();
        }
        $this->actingAs($admin)->post(route('admin.library.store', 'reciters'), [
            'name' => 'New Qari', 'slug' => 'new-qari', 'style' => 'murattal', 'url_pattern' => 'https://example.org/no-placeholder.mp3', 'source' => 'x', 'license_note' => 'x', 'status' => 'in_review',
        ])->assertSessionHasErrors('url_pattern');

        // audio needs a licence and a real slug
        $this->actingAs($admin)->post(route('admin.library.store', 'audio'), ['kind' => 'name', 'ref_slug' => 'amin', 'status' => 'in_review', 'upload' => UploadedFile::fake()->create('a.mp3', 100, 'audio/mpeg')])
            ->assertSessionHasErrors('license');
        $this->actingAs($admin)->post(route('admin.library.store', 'audio'), ['kind' => 'name', 'ref_slug' => 'nobody', 'license' => 'Own', 'status' => 'in_review', 'external_url' => 'https://example.org/a.mp3'])
            ->assertSessionHasErrors('ref_slug');
        $this->actingAs($admin)->post(route('admin.library.store', 'audio'), ['kind' => 'name', 'ref_slug' => 'amin', 'license' => 'Own', 'status' => 'in_review'])
            ->assertSessionHasErrors('upload');
        $this->actingAs($admin)->post(route('admin.library.store', 'audio'), [
            'kind' => 'name', 'ref_slug' => 'amin', 'license' => 'Recorded by our editor, own work', 'speaker' => 'Editor', 'status' => 'published',
            'upload' => UploadedFile::fake()->create('amin.mp3', 120, 'audio/mpeg'),
        ])->assertRedirect();
        $track = AudioTrack::firstOrFail();
        $this->assertSame(NameEntry::where('slug', 'amin')->value('id'), $track->ref_id);
        Storage::disk('public')->assertExists($track->file_path);
        $this->actingAs($admin)->get(route('admin.library.edit', ['audio', $track->id]))->assertOk()->assertSee('value="amin"', false);

        $this->actingAs($admin)->post(route('admin.library.bulk', 'translations'), ['ids' => [QuranTranslation::first()->id], 'action' => 'archived'])->assertRedirect();
        $this->assertSame('archived', QuranTranslation::first()->status);
    }

    public function test_listen_button_on_wishes_messages_quotes_urdu_and_99_names_pages(): void
    {
        $cat = \App\Models\Category::firstOrCreate(['slug' => 'birthday'], ['name' => 'Birthday', 'department' => 'wishes']);
        $en = \App\Models\BirthdayMessage::create(['category_id' => $cat->id, 'relationship' => 'friend', 'tone' => 'funny', 'language' => 'en', 'body' => 'Happy birthday my friend, have a great year!', 'slug' => 'hb-en', 'status' => 'published']);
        $msg = \App\Models\Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Eid wish', 'body' => 'Eid Mubarak to you and your family.', 'slug' => 'eid-1', 'status' => 'published']);
        $u = \App\Models\UrduContent::create(['category' => 'quotes', 'body' => 'زندگی ایک سفر ہے۔', 'slug' => 'ur-1', 'status' => 'published']);
        \App\Models\AllahName::create(['number' => 1, 'arabic' => 'الرَّحْمَٰنُ', 'transliteration' => 'Ar-Rahman', 'meaning_en' => 'The Most Merciful', 'status' => 'published']);

        $this->get(route('birthday-messages.show', $en))->assertOk()->assertSee('Listen (English voice)', false)->assertSee('Choose English voice', false);
        $this->get(route('messages.show', $msg))->assertOk()->assertSee('Listen (English voice)', false);
        $this->get(route('urdu-content.show', $u))->assertOk()->assertSee('Listen (Urdu voice)', false)->assertSee('Choose Urdu voice', false);
        $this->get(route('urdu-content.index'))->assertOk()->assertSee('Listen (Urdu voice)', false);
        $this->get(route('islamic.allah-names'))->assertOk()->assertSee('Listen to Ar-Rahman (Arabic voice)', false);
    }
}
