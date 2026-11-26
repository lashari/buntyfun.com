<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Models\User;
use App\Services\Ads\AdControls;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdControlsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('services.adsense.client_id', 'ca-pub-1234567890123456');
        Config::set('services.adsense.slot_id', '1234567890');
    }

    private function save(array $over = []): \Illuminate\Testing\TestResponse
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $sections = collect(AdControls::SECTIONS)->mapWithKeys(fn ($v, $k) => [$k => '1'])->except('quran')->all();

        return $this->actingAs($admin)->put('/admin/ads', array_merge([
            'ads_enabled' => '1', 'ads_desktop' => '1', 'ads_mobile' => '1', 'ads_max_units' => 1,
            'sections' => $sections, 'excluded_paths' => '',
        ], $over));
    }

    public function test_defaults_show_ads_on_games_but_not_quran(): void
    {
        $this->get('/games')->assertOk()->assertSee('adsbygoogle.js', false)->assertSee('data-ad-slot="1234567890"', false);
        $this->assertSame('quran', AdControls::sectionFor('quran.surah'));
        $this->assertSame('games', AdControls::sectionFor('games.show'));
        $this->assertSame('home', AdControls::sectionFor('home'));
        $this->assertSame('other', AdControls::sectionFor('pages.about'));
        $this->assertFalse(Setting::DEFAULTS['ads_sections']['quran']);
    }

    public function test_master_switch_and_section_switch_remove_script_and_unit(): void
    {
        $this->save(['ads_enabled' => null])->assertRedirect();
        $this->get('/games')->assertDontSee('adsbygoogle', false);

        $this->save(['sections' => ['home' => '1']])->assertRedirect();
        $this->get('/games')->assertDontSee('adsbygoogle', false);
        $this->get('/')->assertSee('adsbygoogle.js', false);
    }

    public function test_excluded_paths_and_validation(): void
    {
        $this->save(['excluded_paths' => "/games\n"])->assertSessionHasNoErrors();
        $this->get('/games')->assertDontSee('adsbygoogle', false);
        $this->assertTrue(AdControls::pathExcluded('games/ludo', ['/games']));
        $this->assertFalse(AdControls::pathExcluded('games-2', ['/games']));
        $this->assertTrue(AdControls::pathExcluded('', ['/']));
        $this->assertFalse(AdControls::pathExcluded('games', ['/']));

        $this->save(['excluded_paths' => "<script>"])->assertSessionHasErrors('excluded_paths');
        $this->save(['ads_max_units' => 9])->assertSessionHasErrors('ads_max_units');
    }

    public function test_devices_and_unit_limit(): void
    {
        $this->save(['ads_mobile' => null]);
        $this->get('/games')->assertSee('hidden md:block', false);
        $this->save(['ads_desktop' => null]);
        $this->get('/games')->assertSee('class="mx-auto my-8 max-w-3xl px-4 md:hidden"', false);
        $this->save(['ads_desktop' => null, 'ads_mobile' => null]);
        $this->get('/games')->assertDontSee('adsbygoogle', false);

        $this->save(['ads_max_units' => 0]);
        $this->get('/games')->assertSee('adsbygoogle.js', false)->assertDontSee('<ins class="adsbygoogle"', false);
    }

    public function test_only_super_admin_can_save_and_page_shows_status(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);
        $this->actingAs($editor)->get('/admin/ads')->assertOk()->assertSee('Only a super admin');
        $this->actingAs($editor)->put('/admin/ads', ['ads_max_units' => 1])->assertForbidden();
        $this->get('/admin/ads')->assertSee('set ✓');
        auth()->logout();
        $this->get('/admin/ads')->assertRedirect();
    }
}
