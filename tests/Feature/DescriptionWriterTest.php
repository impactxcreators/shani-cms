<?php

namespace Tests\Feature;

use App\Filament\Pages\DescriptionWriter;
use App\Filament\Pages\ManageDescriptionWriter;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Coupon;
use App\Models\DescriptionJob;
use App\Models\Product;
use App\Models\RefererLink;
use App\Models\Store;
use App\Models\User;
use App\Support\Describer\Describer;
use App\Support\Describer\SiteReader;
use App\Support\Describer\Text;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class DescriptionWriterTest extends TestCase
{
    use RefreshDatabase;

    private const SHOP_HTML = <<<'HTML'
<html><head>
<title>CustomBoxes.io | Custom Printed Packaging for Brands</title>
<meta name="description" content="We design and print custom kraft boxes, mailer boxes and shipping boxes for small businesses. Order from just 50 boxes with free design support.">
<meta property="og:site_name" content="CustomBoxes.io">
</head><body>
<nav><ul><li><a href="/">Home</a></li><li><a href="/shop">Shop</a></li></ul></nav>
<h1>Custom Boxes Made Simple</h1>
<p>Our boxes are made from recycled cardboard and printed with eco-friendly inks, so your packaging looks great and stays sustainable.</p>
<h2>Free Shipping on Every Order</h2>
<ul><li>Low minimum order quantities</li><li>Full-color printing inside and out</li><li>Free 3D design proof</li></ul>
<p>Trusted by 10,000+ happy customers since 2015. Enjoy a 30-day money-back guarantee and 24/7 support.</p>
<footer>© 2026 CustomBoxes.io. All rights reserved. Cookie settings.</footer>
</body></html>
HTML;

    private function fakeShop(): void
    {
        Cache::flush();
        Http::fake([
            'https://customboxes.example/*' => Http::response(self::SHOP_HTML, 200),
            'https://blocked.example/*' => Http::response('Forbidden', 403),
        ]);
    }

    public function test_text_helpers(): void
    {
        $this->assertSame('an', Text::an('eco-friendly'));
        $this->assertSame('a', Text::an('useful'));
        $this->assertSame('a', Text::an('28-day'));
        $this->assertSame('a, b and c', Text::listJoin(['a', 'b', 'c']));
        $this->assertSame('custom kraft boxes for SUNLU and 3D WordPress', Text::soften('Custom Kraft Boxes for SUNLU and 3D WordPress'));
        $this->assertSame('carries', Text::thirdPerson('carry'));
    }

    public function test_reader_understands_a_shop_page(): void
    {
        $info = (new SiteReader)->parse(self::SHOP_HTML, 'https://customboxes.example/');

        $this->assertSame('CustomBoxes.io', $info['shop']);
        $this->assertSame('custom kraft boxes, mailer boxes and shipping boxes', $info['offering']);
        $this->assertContains('free shipping', $info['perks']);
        $this->assertContains('a 30-day money-back guarantee', $info['perks']);
        $this->assertNotContains('30-day returns', $info['perks']);
        $this->assertContains('round-the-clock support', $info['perks']);
        $this->assertContains('eco-friendly materials', $info['perks']);
        $this->assertSame('10,000+', $info['customers']);
        $this->assertSame('2015', $info['since']);
        $this->assertContains('Low minimum order quantities', $info['features']);
        $this->assertNotContains('Home', $info['features']);
    }

    public function test_links_are_resolved_without_affiliate_clicks(): void
    {
        $r = new SiteReader;
        [$url] = $r->resolve('https://www.awin1.com/cread.php?awinmid=1&awinaffid=2&ued=https%3A%2F%2Fshop.example%2Fitem%3Fx%3D1');
        $this->assertSame('https://shop.example/item?x=1', $url);

        [$url] = $r->resolve('https://collegewares.com?sca_ref=768409.scRgqOGtA9&utm_source=aff&size=xl');
        $this->assertSame('https://collegewares.com/?size=xl', $url);

        $link = RefererLink::create(['type' => 'direct', 'code' => 'abc123']);
        $link->destinations()->create(['url' => 'https://shop.example/page']);
        [$url] = $r->resolve(url('/ref/abc123'));
        $this->assertSame('https://shop.example/page', $url);
        $this->assertSame(0, (int) $link->fresh()->click_count);
    }

    public function test_product_description_reads_naturally(): void
    {
        $this->fakeShop();
        $p = Product::create(['title' => 'CustomBoxes.io – Best for Custom Kraft Boxes', 'affiliate_url' => 'https://customboxes.example/kraft?ref=me', 'price' => 49]);

        $r = app(Describer::class)->describe($p, 'medium');
        $text = $r['text'];

        $this->assertTrue($r['site']['ok']);
        $this->assertStringStartsWith('<p>', $text);
        $this->assertStringContainsString('custom kraft boxes', $text);
        $this->assertStringContainsString('CustomBoxes.io', $text);
        // the shop's own first-person wording is rewritten ("We design and print" → "CustomBoxes.io designs and prints")
        $this->assertDoesNotMatchRegularExpression('/\bWe design|\bOur boxes/', strip_tags($text));
        $words = str_word_count(strip_tags(str_replace('</p>', ' ', $text)));
        $this->assertGreaterThanOrEqual(40, $words);
        $this->assertLessThanOrEqual(140, $words);

        $long = app(Describer::class)->describe($p, 'long')['text'];
        $this->assertStringContainsString('<ul><li>', $long);

        $short = app(Describer::class)->describe($p, 'short')['text'];
        $this->assertLessThan(str_word_count(strip_tags($text)), str_word_count(strip_tags($short)) + 1);

        // different seeds give different versions
        $versions = collect(range(1, 6))->map(fn ($s) => app(Describer::class)->describe($p, 'medium', $s * 1000)['text'])->unique();
        $this->assertGreaterThan(2, $versions->count());
    }

    public function test_blocked_site_still_gets_a_description(): void
    {
        $this->fakeShop();
        $p = Product::create(['title' => 'Klook – Best for Theme Park Tickets', 'affiliate_url' => 'https://blocked.example/']);

        $r = app(Describer::class)->describe($p, 'medium');
        $this->assertFalse($r['site']['ok']);
        $this->assertStringContainsString('blocks automatic visits', $r['note']);
        $this->assertStringContainsString('theme park tickets', $r['text']);
    }

    public function test_coupon_description_is_plain_text_with_code(): void
    {
        $this->fakeShop();
        $store = Store::create(['name' => 'CustomBoxes.io', 'slug' => 'cb', 'status' => 'published', 'website_url' => 'https://customboxes.example/']);
        $c = Coupon::create(['store_id' => $store->id, 'title' => '20% off your first order', 'code' => 'BOX20', 'discount_type' => 'percent', 'discount_value' => 20, 'is_verified' => true, 'status' => 'published']);

        $text = app(Describer::class)->describe($c, 'medium')['text'];
        $this->assertStringNotContainsString('<p>', $text);
        $this->assertStringContainsString('BOX20', $text);
        $this->assertStringContainsString('20% off', $text);
    }

    public function test_bulk_run_writes_skips_and_undoes(): void
    {
        $this->fakeShop();
        $this->actingAs(User::factory()->create());
        $original = Product::create(['title' => 'CustomBoxes.io – Best for Mailer Boxes', 'affiliate_url' => 'https://customboxes.example/', 'description' => '<p>Original text.</p>']);
        $copy = Product::create(['title' => 'CustomBoxes.io – Best for Mailer Boxes', 'affiliate_url' => 'https://customboxes.example/mailer', 'description' => '<p>Original text.</p>', 'copied_from_id' => $original->id]);
        $own = Product::create(['title' => 'Other', 'affiliate_url' => 'https://customboxes.example/', 'description' => '<p>My own words.</p>']);

        Livewire::test(ListProducts::class)
            ->callTableBulkAction('writeDescriptions', [$copy, $own], data: ['length' => 'medium', 'overwrite' => false])
            ->assertHasNoTableBulkActionErrors();
        $batch = DescriptionJob::value('batch');

        Livewire::test(DescriptionWriter::class, ['batch' => $batch])->call('step')->assertSee('2 of 2 done');

        $this->assertNotSame('<p>Original text.</p>', $copy->fresh()->description);
        $this->assertSame('<p>My own words.</p>', $own->fresh()->description);
        $this->assertSame('skipped', DescriptionJob::where('model_id', $own->id)->value('status'));

        Livewire::test(DescriptionWriter::class, ['batch' => $batch])->call('undoAll');
        $this->assertSame('<p>Original text.</p>', $copy->fresh()->description);
    }

    public function test_admin_screens_load(): void
    {
        $this->fakeShop();
        $this->actingAs(User::factory()->create());
        $p = config('cms.filament_path', 'admin');
        $product = Product::create(['title' => 'X', 'affiliate_url' => 'https://customboxes.example/']);

        $this->get("/{$p}/products/{$product->id}/edit")->assertOk()->assertSee('Write from link');
        $this->get("/{$p}/description-writer")->assertOk();
        $this->get("/{$p}/manage-description-writer")->assertOk();

        Livewire::test(ManageDescriptionWriter::class)
            ->set('previewTitle', 'CustomBoxes.io – Best for Mailer Boxes')
            ->set('previewLink', 'https://customboxes.example/')
            ->call('preview')
            ->assertSet('previewNote', 'Read from CustomBoxes.io.')
            ->assertSee('mailer boxes');
    }
}
