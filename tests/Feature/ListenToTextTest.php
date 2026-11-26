<?php

namespace Tests\Feature;

use App\Models\Dua;
use App\Models\Message;
use App\Models\Poem;
use App\Models\Poet;
use App\Models\UrduContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ListenToTextTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::flush();
    }

    private function html(string $url): string
    {
        $html = $this->get($url)->assertOk()->getContent();

        $html = preg_replace('#\\\\+/#', '/', str_replace(['\\u0022', '\\u0026'], ['"', '&'], $html));

        // JSON handed to Alpine: \\uXXXX → the character, \\n → newline
        return preg_replace('/\\\\+n/', "\n", preg_replace_callback('/\\\\+u([0-9a-f]{4})/i', fn ($m) => mb_chr(hexdec($m[1])), $html));
    }

    public function test_listen_page_renders_player_upload_and_honest_voice_notes(): void
    {
        $h = $this->html('/listen');
        $this->assertStringContainsString('<h1 class="text-2xl font-bold text-navy-700 sm:text-3xl">Listen to Text</h1>', $h);
        $this->assertStringContainsString('x-data="listenPlayer(', $h);
        $this->assertStringContainsString('accept=".txt,.pdf,.docx', $h);
        foreach (['Speed', 'Auto (mixed)', 'Urdu اردو', 'Arabic العربية', 'not uploaded', 'never call a voice Pakistani', 'Nothing to set up', 'More options', 'not a substitute'] as $s) {
            $this->assertStringContainsString($s, $h);
        }
        $this->assertStringContainsString('"@type":"WebApplication"', $h);
        $this->assertStringNotContainsString('noindex', $h);
        $this->assertStringContainsString('<link rel="canonical" href="'.route('listen').'"', $h);
        $this->assertStringNotContainsString('Egyptian', $h);
        $this->assertStringNotContainsString('Misri', $h);
    }

    public function test_listen_is_in_sitemap_and_nav(): void
    {
        $this->assertStringContainsString('<loc>'.route('listen').'</loc>', $this->get('/sitemap.xml')->getContent());
        $this->assertStringContainsString('href="'.route('listen').'"', $this->get('/')->getContent());
    }

    public function test_from_loads_published_content_and_is_noindex(): void
    {
        $poet = Poet::create(['slug' => 'ghalib', 'name_en' => 'Mirza Ghalib', 'name_ur' => 'غالب']);
        Poem::create(['poet_id' => $poet->id, 'slug' => 'hazaron', 'title' => 'Hazaron khwahishen', 'form' => 'ghazal', 'body' => "ہزاروں خواہشیں ایسی\nکہ ہر خواہش پہ دم نکلے\n\nبہت نکلے مرے ارمان", 'line_count' => 3, 'hash' => 'h1', 'status' => 'published']);
        $h = $this->html('/listen?from=poem:hazaron');
        $this->assertStringContainsString('Hazaron khwahishen — Mirza Ghalib', $h);
        $this->assertStringContainsString("ہزاروں خواہشیں ایسی\nکہ ہر خواہش پہ دم نکلے\n\nبہت نکلے مرے ارمان", $h);
        $this->assertStringContainsString('noindex', $h);
        $this->assertStringContainsString(route('poetry.show', ['ghalib', 'hazaron']), $h);

        UrduContent::create(['category' => 'quotes', 'body' => 'زندگی ایک سفر ہے۔', 'slug' => 'ur-1', 'status' => 'published']);
        $this->assertStringContainsString('زندگی ایک سفر ہے۔', $this->html('/listen?from=urdu:ur-1'));
        Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Eid Joy', 'body' => 'Eid Mubarak to you.', 'slug' => 'eid-joy', 'status' => 'published']);
        $this->assertStringContainsString('Eid Mubarak to you.', $this->html('/listen?from=message:eid-joy'));
    }

    public function test_unpublished_or_unknown_content_is_not_loaded(): void
    {
        Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Draft', 'body' => 'SECRET DRAFT BODY', 'slug' => 'draft-x', 'status' => 'draft']);
        $this->assertStringNotContainsString('SECRET DRAFT BODY', $this->html('/listen?from=message:draft-x'));
        $this->get('/listen?from=nope:x')->assertOk();
        $this->get('/listen?from=../../etc')->assertOk();
        $this->get('/listen?from[]=x')->assertOk();
        $this->get('/listen?from=quote:abc')->assertOk();
    }

    public function test_quranic_dua_gives_only_translation_never_arabic_for_tts(): void
    {
        Dua::create(['slug' => 'rabbana-atina', 'title' => 'Rabbana atina', 'category' => 'quran', 'arabic' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً',
            'translation_en' => 'Our Lord, give us good in this world.', 'reference' => 'Quran 2:201', 'status' => 'published']);
        Dua::create(['slug' => 'before-sleep', 'title' => 'Before sleeping', 'category' => 'sleep', 'arabic' => 'بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا',
            'translation_en' => 'In Your name, O Allah, I die and I live.', 'reference' => 'Sahih al-Bukhari 6324', 'status' => 'published']);

        $q = $this->html('/listen?from=dua:rabbana-atina');
        $this->assertStringContainsString('Our Lord, give us good in this world.', $q);
        $this->assertStringNotContainsString('رَبَّنَا آتِنَا', $q);
        $this->assertStringContainsString('a computer voice does not read its Arabic', $q);

        $d = $this->html('/listen?from=dua:before-sleep');
        $this->assertStringContainsString('بِاسْمِكَ اللَّهُمَّ أَمُوتُ وَأَحْيَا', $d);
        $this->assertStringContainsString('In Your name, O Allah', $d);
        $this->assertStringContainsString('Computer voices can mispronounce Arabic', $d);

        // try-list: only the non-Quranic dua
        $h = $this->html('/listen');
        $this->assertStringContainsString('from=dua%3Abefore-sleep', $h);
        $this->assertStringNotContainsString('from=dua%3Arabbana-atina', $h);
    }

    public function test_content_pages_link_to_the_player(): void
    {
        Message::create(['category' => 'eid', 'language' => 'en', 'title' => 'Eid Joy', 'body' => 'Eid Mubarak to you.', 'slug' => 'eid-joy', 'status' => 'published']);
        $this->assertStringContainsString(route('listen', ['from' => 'message:eid-joy']), $this->get(route('messages.show', 'eid-joy'))->getContent());
        Dua::create(['slug' => 'before-sleep', 'title' => 'Before sleeping', 'category' => 'sleep', 'arabic' => 'بِاسْمِكَ اللَّهُمَّ',
            'translation_en' => 'In Your name.', 'reference' => 'Sahih al-Bukhari 6324', 'status' => 'published']);
        $this->assertStringContainsString(route('listen', ['from' => 'dua:before-sleep']), $this->get(route('duas.show', 'before-sleep'))->getContent());
    }
}
