<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfferFeedTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('maintenance_mode', '0', 'bool');
        Setting::set('site_name', 'Gipsy Pockets');
    }

    private function store(string $name, ?Category $category = null, array $extra = []): Store
    {
        $store = Store::create(array_merge(['name' => $name, 'slug' => \Illuminate\Support\Str::slug($name),
            'status' => 'published', 'is_active' => true], $extra));
        if ($category) {
            $store->categories()->attach($category);
        }

        return $store;
    }

    private function coupon(Store $store, string $title, array $extra = []): Coupon
    {
        return Coupon::create(array_merge(['store_id' => $store->id, 'title' => $title, 'status' => 'published'], $extra));
    }

    private function deal(Store $store, string $title, array $extra = []): Deal
    {
        return Deal::create(array_merge(['store_id' => $store->id, 'title' => $title, 'status' => 'published'], $extra));
    }

    public function test_category_page_lists_offers_from_its_stores_and_subcategories(): void
    {
        $fashion = Category::create(['name' => 'Fashion']);
        $shoes = Category::create(['name' => 'Shoes', 'parent_id' => $fashion->id]);
        $tech = Category::create(['name' => 'Tech']);

        $nike = $this->store('Nike', $shoes);
        $thread = $this->store('Urban Thread', $fashion);
        $hidden = $this->store('Hidden Store', $fashion, ['status' => 'draft']);
        $gadget = $this->store('GadgetPoint', $tech);

        $this->coupon($nike, 'Nike 30 percent coupon', ['code' => 'NIKE30', 'discount_type' => 'percentage', 'discount_value' => 30, 'is_verified' => true]);
        $this->deal($thread, 'Thread jacket deal', ['original_price' => 100, 'sale_price' => 75]);
        $this->coupon($thread, 'Expired thread coupon', ['expires_at' => now()->subDay()]);
        $this->coupon($hidden, 'Coupon from a draft store');
        $this->coupon($gadget, 'Gadget coupon');

        $res = $this->get('/category/fashion')->assertOk();
        $res->assertSee('Nike 30 percent coupon')->assertSee('Thread jacket deal');
        $res->assertDontSee('Expired thread coupon')->assertDontSee('Coupon from a draft store')->assertDontSee('Gadget coupon');
        $res->assertSee('30%')->assertSee('25%');
        $res->assertSee('Verified');
        $res->assertSee('Show code')->assertSee('Show deal');
        $res->assertSee('Gipsy Pockets has 1 active coupon and 1 deal from 2 Fashion stores. 1 coupon is verified by our team.');
        $res->assertSee(route('store.show', 'nike'), false);
        $res->assertSee(route('category.show', 'shoes'), false);
    }

    public function test_type_filter_and_sorting(): void
    {
        $cat = Category::create(['name' => 'Fashion']);
        $store = $this->store('Nike', $cat);
        $this->coupon($store, 'Small coupon', ['discount_type' => 'percentage', 'discount_value' => 10]);
        $this->coupon($store, 'Big coupon', ['discount_type' => 'percentage', 'discount_value' => 60]);
        $this->deal($store, 'Middle deal', ['original_price' => 100, 'sale_price' => 70]);

        $this->get('/category/fashion?type=coupons')->assertOk()
            ->assertSee('Big coupon')->assertDontSee('Middle deal');
        $this->get('/category/fashion?type=deals')->assertOk()
            ->assertSee('Middle deal')->assertDontSee('Big coupon');

        $this->get('/category/fashion?sort=discount')->assertOk()
            ->assertSeeInOrder(['Big coupon', 'Middle deal', 'Small coupon']);

        // Unknown values fall back to the defaults instead of erroring.
        $this->get('/category/fashion?sort=evil&type[]=x')->assertOk()->assertSee('Big coupon');
    }

    public function test_ending_soon_puts_dated_offers_first(): void
    {
        $cat = Category::create(['name' => 'Fashion']);
        $store = $this->store('Nike', $cat);
        $this->coupon($store, 'No end date');
        $this->coupon($store, 'Ends next month', ['expires_at' => now()->addMonth()]);
        $this->deal($store, 'Ends tomorrow', ['expires_at' => now()->addDay()]);

        $this->get('/category/fashion?sort=ending')->assertOk()
            ->assertSeeInOrder(['Ends tomorrow', 'Ends next month', 'No end date'])
            ->assertSee('Ends soon');
    }

    public function test_pages_twenty_at_a_time_with_load_more(): void
    {
        $cat = Category::create(['name' => 'Fashion']);
        $store = $this->store('Nike', $cat);
        foreach (range(1, 25) as $i) {
            $this->coupon($store, "Offer number {$i}");
        }

        $first = $this->get('/category/fashion')->assertOk()->getContent();
        $this->assertSame(20, substr_count($first, '<article class="offer"'));
        $this->assertStringContainsString('data-loadmore>Load more', $first);

        $second = $this->get('/category/fashion?page=2')->assertOk()->getContent();
        $this->assertSame(5, substr_count($second, '<article class="offer"'));
        $this->assertStringNotContainsString('data-loadmore>Load more', $second);
    }

    public function test_coupons_and_deals_pages_use_offer_rows(): void
    {
        $store = $this->store('Nike');
        $this->coupon($store, 'Sitewide coupon', ['code' => 'ALL10']);
        $this->deal($store, 'Sitewide deal', ['original_price' => 50, 'sale_price' => 40]);
        $draft = $this->store('Draft', null, ['status' => 'draft']);
        $this->coupon($draft, 'Draft store coupon');

        $this->get('/coupons')->assertOk()
            ->assertSee('Sitewide coupon')->assertDontSee('Sitewide deal')->assertDontSee('Draft store coupon')
            ->assertSee('<article class="offer"', false)
            ->assertSee('Gipsy Pockets has 1 active coupon from 1 store.');

        $this->get('/deals')->assertOk()
            ->assertSee('Sitewide deal')->assertDontSee('Sitewide coupon')
            ->assertSee('20%');
    }

    public function test_sitemap_category_names_link_to_their_pages(): void
    {
        $cat = Category::create(['name' => 'Fashion']);
        $this->store('Nike', $cat);

        $this->get('/sitemap')->assertOk()
            ->assertSee('<a class="smname" href="'.route('category.show', 'fashion').'">Fashion</a>', false)
            ->assertSee(route('store.show', 'nike'), false);
    }
}
