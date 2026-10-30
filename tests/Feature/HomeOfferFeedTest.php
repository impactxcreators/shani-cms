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

        $this->get('/?type=deals')->assertOk()->assertSee('Newest deal')->assertDontSee('Coupon 4');
    }
}
