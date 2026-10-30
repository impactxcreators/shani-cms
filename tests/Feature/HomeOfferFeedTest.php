<?php

namespace Tests\Feature;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\HomeBlock;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeOfferFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_offer_feed_lists_newest_coupons_and_deals_with_load_more(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $store = Store::create(['name' => 'Nike', 'slug' => 'nike', 'status' => 'published', 'is_active' => true]);
        foreach (range(1, 4) as $i) {
            Coupon::create(['store_id' => $store->id, 'title' => "Coupon {$i}", 'status' => 'published', 'created_at' => now()->subDays(10 - $i)]);
        }
        Deal::create(['store_id' => $store->id, 'title' => 'Newest deal', 'status' => 'published', 'original_price' => 80, 'sale_price' => 60]);
        HomeBlock::create(['type' => 'offer_feed', 'title' => 'Latest coupons & deals', 'config' => ['limit' => 3],
            'is_active' => true, 'sort_order' => 1]);

        $first = $this->get('/')->assertOk()
            ->assertSee('Latest coupons &amp; deals', false)
            ->assertSeeInOrder(['Newest deal', 'Coupon 4', 'Coupon 3'])
            ->assertDontSee('Coupon 1')
            ->assertSee('data-loadmore>Load more', false)
            ->getContent();
        $this->assertSame(3, substr_count($first, '<article class="offer"'));

        $this->get('/?page=2')->assertOk()->assertSee('Coupon 2')->assertSee('Coupon 1')
            ->assertDontSee('data-loadmore>Load more', false);

        // Home uses the Hot / Newest sort only, without type filters.
        $this->get('/?sort=new')->assertOk()->assertSee('<option value="new" selected', false)
            ->assertDontSee('Biggest discount')->assertDontSee('class="chip');
    }

    public function test_home_sections_in_curateddeals_order(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Setting::set('site_name', 'Gipsy Pockets');
        $fashion = \App\Models\Category::create(['name' => 'Fashion', 'is_featured' => true]);
        \App\Models\Category::create(['name' => 'Shoes', 'parent_id' => $fashion->id]);
        $store = Store::create(['name' => 'Nike', 'slug' => 'nike', 'status' => 'published', 'is_active' => true]);
        $store->categories()->attach($fashion);
        Coupon::create(['store_id' => $store->id, 'title' => 'Nike code', 'code' => 'N10', 'status' => 'published', 'is_verified' => true]);

        $blocks = [
            ['intro', 'Save more today', []],
            ['offer_feed', null, ['limit' => 20]],
            ['category_boxes', null, []],
            ['stores_by_category', null, []],
            ['why_us', null, ['points' => [['icon' => '*', 'title' => 'Fast savings', 'text' => 'Quick.']]]],
            ['faq', null, []],
        ];
        foreach ($blocks as $i => [$type, $title, $config]) {
            HomeBlock::create(['type' => $type, 'title' => $title, 'config' => $config, 'is_active' => true, 'sort_order' => $i]);
        }

        $res = $this->get('/')->assertOk();
        $res->assertSeeInOrder(['<h1>Save more today</h1>', 'Nike code', 'Shop by category', 'Featured stores by category', 'Fast savings', 'Frequently asked questions'], false);
        $res->assertSee('Gipsy Pockets has 1 active coupon from 1 store across 2 categories. 1 coupon is verified by our team.');
        $res->assertSee(route('category.show', 'shoes'), false);
        $res->assertSee('1 coupon');
        $res->assertSee('"@type":"FAQPage"', false)->assertSee('What is a coupon code?');
        $res->assertDontSee('Every offer in one place');
    }
}
