<?php

namespace Tests\Feature;

use App\Filament\Resources\RefererLinks\Pages\CreateRefererLink;
use App\Filament\Resources\RefererLinks\Pages\ListRefererLinks;
use App\Models\RefererLink;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class RefererLinksTest extends TestCase
{
    use RefreshDatabase;

    private const BROWSER = ['User-Agent' => 'Mozilla/5.0 (Windows NT 10.0) Chrome/130.0'];

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('maintenance_mode', '0', 'bool');
    }

    private function link(string $type, array $urls, array $attrs = []): RefererLink
    {
        $link = RefererLink::create(['type' => $type] + $attrs);
        foreach ($urls as $i => $u) {
            $link->destinations()->create(is_array($u) ? $u + ['sort_order' => $i] : ['url' => $u, 'sort_order' => $i]);
        }

        return $link->fresh();
    }

    public function test_direct_link_redirects_and_counts_the_click(): void
    {
        $link = $this->link('direct', ['https://merchant.example/aff?id=1']);
        $this->assertSame(11, strlen($link->code));

        $this->get('/ref/'.$link->code, self::BROWSER)
            ->assertRedirect('https://merchant.example/aff?id=1')
            ->assertHeader('X-Robots-Tag', 'noindex, nofollow');

        $this->assertSame(1, (int) $link->fresh()->click_count);
        $this->assertDatabaseHas('referer_clicks', ['referer_link_id' => $link->id, 'clicks' => 1]);
        $this->get('/ref/'.$link->code, self::BROWSER);
        $this->assertDatabaseHas('referer_clicks', ['referer_link_id' => $link->id, 'clicks' => 2]);
    }

    public function test_bots_are_not_counted(): void
    {
        $link = $this->link('direct', ['https://merchant.example/']);
        $this->get('/ref/'.$link->code, ['User-Agent' => 'Googlebot/2.1'])->assertRedirect('https://merchant.example/');
        $this->assertSame(0, (int) $link->fresh()->click_count);
    }

    public function test_blank_link_hides_the_referrer(): void
    {
        $link = $this->link('blank', ['https://merchant.example/x']);
        $this->get('/ref/'.$link->code, self::BROWSER)
            ->assertOk()
            ->assertHeader('Referrer-Policy', 'no-referrer')
            ->assertSee('<meta name="referrer" content="no-referrer">', false)
            ->assertSee('https://merchant.example/x', false);
    }

    public function test_rotational_in_order_goes_to_a_new_site_each_click(): void
    {
        $link = $this->link('rotational', ['https://a.example/', 'https://b.example/', 'https://c.example/'], ['rotation_mode' => 'in_order']);
        $seen = collect(range(1, 4))->map(fn () => $this->get('/ref/'.$link->code, self::BROWSER)->headers->get('Location'))->all();
        $this->assertSame(['https://a.example/', 'https://b.example/', 'https://c.example/', 'https://a.example/'], $seen);
    }

    public function test_paused_and_zero_weight_destinations_are_skipped(): void
    {
        $random = $this->link('rotational', ['https://a.example/', ['url' => 'https://b.example/', 'is_active' => false]], ['rotation_mode' => 'random']);
        $weighted = $this->link('rotational', [['url' => 'https://a.example/', 'weight' => 0], ['url' => 'https://b.example/', 'weight' => 5]], ['rotation_mode' => 'weighted']);

        foreach (range(1, 10) as $i) {
            $this->assertSame('https://a.example/', $this->get('/ref/'.$random->code, self::BROWSER)->headers->get('Location'));
            $this->assertSame('https://b.example/', $this->get('/ref/'.$weighted->code, self::BROWSER)->headers->get('Location'));
        }
    }

    public function test_off_expired_and_unknown_links_use_the_fallback(): void
    {
        $off = $this->link('direct', ['https://a.example/'], ['is_active' => false]);
        $expired = $this->link('direct', ['https://a.example/'], ['expires_at' => now()->subDay(), 'fallback_url' => 'https://gipsypockets.store/deals']);

        $this->get('/ref/'.$off->code)->assertRedirect(url('/'));
        $this->get('/ref/'.$expired->code)->assertRedirect('https://gipsypockets.store/deals');
        $this->get('/ref/doesNotExist')->assertRedirect('/');
    }

    public function test_custom_codes_with_slashes_and_case_work(): void
    {
        $a = $this->link('direct', ['https://a.example/'], ['code' => 'myqg6Pc/Mlk2w']);
        $b = $this->link('direct', ['https://b.example/'], ['code' => 'MYQG6PC/mlk2w']);
        $this->get('/ref/myqg6Pc/Mlk2w', self::BROWSER)->assertRedirect('https://a.example/');
        $this->get('/ref/MYQG6PC/mlk2w', self::BROWSER)->assertRedirect('https://b.example/');
    }

    public function test_bulk_add_direct_makes_one_link_per_line_and_reports_duplicates(): void
    {
        $existing = $this->link('direct', ['https://drinkbev.pxf.io/b3RYrx']);

        $r = RefererLink::bulkCreate('direct', null, "https://drinkbev.pxf.io/b3RYrx\n r-bloop.cc/9Mwz92Yi?sc=cl \n\nnot a link at all");

        $this->assertSame(2, $r['created']);
        $this->assertSame(['not a link at all'], $r['invalid']);
        $this->assertSame(3, RefererLink::count());
        $this->assertDatabaseHas('referer_destinations', ['url' => 'https://r-bloop.cc/9Mwz92Yi?sc=cl']);
        $this->assertSame([$existing->id], RefererLink::existingFor('https://drinkbev.pxf.io/b3RYrx', null, $r['batch'])->pluck('id')->all());
    }

    public function test_bulk_add_rotational_makes_one_rotating_link(): void
    {
        $r = RefererLink::bulkCreate('rotational', 'random', "https://a.example/\nhttps://b.example/\nhttps://c.example/");
        $this->assertSame(1, RefererLink::count());
        $link = RefererLink::first();
        $this->assertSame('random', $link->rotation_mode);
        $this->assertSame(3, $link->destinations()->count());
    }

    public function test_admin_can_add_a_link_and_bulk_add_shows_results(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(CreateRefererLink::class)
            ->fillForm(['type' => 'direct', 'name' => 'Awin Nike'])
            ->set('data.destinations', [['url' => 'www.awin1.com/cread.php?awinmid=1', 'weight' => 1, 'is_active' => true]])
            ->call('create')
            ->assertHasNoFormErrors();
        $this->assertDatabaseHas('referer_destinations', ['url' => 'https://www.awin1.com/cread.php?awinmid=1']);

        Livewire::test(ListRefererLinks::class)
            ->callAction('bulk', data: ['type' => 'direct', 'links' => "https://a.example/\nhttps://b.example/"])
            ->assertHasNoActionErrors();
        $batch = RefererLink::whereNotNull('batch')->value('batch');

        $p = config('cms.filament_path', 'admin');
        $this->get("/{$p}/referer-links")->assertOk();
        $this->get("/{$p}/referer-links/batch/{$batch}")->assertOk()->assertSee('2 short link(s) created')->assertSee('https://b.example/');
        $this->get("/{$p}/referer-links/create")->assertOk();
    }

    public function test_link_checker_flags_dead_destinations(): void
    {
        Http::fake(['https://dead.example/*' => Http::response('', 404), 'https://fine.example/*' => Http::response('ok', 200)]);
        $dead = $this->link('direct', ['https://dead.example/x']);
        $fine = $this->link('direct', ['https://fine.example/x']);

        $this->artisan('referers:check')->assertSuccessful();

        $this->assertSame('broken', $dead->destinations()->first()->check_status);
        $this->assertSame('ok', $fine->destinations()->first()->check_status);
    }

    public function test_store_form_has_referer_picker_and_robots_blocks_ref(): void
    {
        $this->actingAs(User::factory()->create());
        $store = Store::create(['name' => 'S', 'slug' => 's', 'status' => 'published']);
        $p = config('cms.filament_path', 'admin');
        $this->get("/{$p}/stores/{$store->id}/edit")->assertOk();
        $this->get('/robots.txt')->assertSee('Disallow: /ref/');
    }
}
