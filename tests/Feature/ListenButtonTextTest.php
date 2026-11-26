<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/** Listen buttons must not carry "no voice" warnings in the page HTML (crawlers, reader mode, copy-paste see them). */
class ListenButtonTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_speak_button_has_no_static_warning_and_hides_when_unsupported(): void
    {
        $html = Blade::render('<x-speak text="گو جوانی میں تھی کج رائی بہت" lang="ur" />');
        $this->assertStringNotContainsString('voice on this device', $html);
        $this->assertStringContainsString('BF.canSpeak(lang)', $html);
        $this->assertStringContainsString('x-show="can"', $html);
        $this->assertStringContainsString('bf-voice-failed', $html);
        $this->assertSame(0, preg_match('/>[^<]*did not load[^<]*</', $html), 'retry text must be set by script only');
    }

    public function test_home_page_text_has_no_voice_warning(): void
    {
        $this->get('/')->assertOk()->assertDontSee('No Urdu voice on this device');
    }
}
