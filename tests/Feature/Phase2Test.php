<?php

namespace Tests\Feature;

use App\Models\ClickEvent;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase2Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    private function store(): Store
    {
        return Store::create(['name' => 'Nike', 'slug' => 'nike', 'status' => 'published', 'is_active' => true,
            'affiliate_url' => 'https://nike.com/?aff=x']);
    }

    public function test_admin_commerce_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());
        $p = $this->path();
        foreach (['stores', 'categories', 'coupons', 'deals'] as $r) {
            $this->get("/{$p}/{$r}")->assertStatus(200);
            $this->get("/{$p}/{$r}/create")->assertStatus(200);
        }
    }

    public function test_store_page_shows_active_coupons(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $store = $this->store();
        Coupon::create(['store_id' => $store->id, 'title' => '20% off', 'code' => 'SAVE20', 'status' => 'published']);
        Coupon::create(['store_id' => $store->id, 'title' => 'Expired one', 'code' => 'OLD', 'status' => 'published', 'expires_at' => now()->subDay()]);

        $res = $this->get('/store/nike')->assertStatus(200);
        $res->assertSee('20% off');
        $res->assertDontSee('Expired one');
    }

    public function test_go_link_logs_click_and_redirects(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $store = $this->store();
        $coupon = Coupon::create(['store_id' => $store->id, 'title' => 'Deal', 'status' => 'published',
            'destination_url' => 'https://nike.com/offer']);

        $this->get("/go/coupon/{$coupon->id}")
            ->assertStatus(302)
            ->assertRedirect('https://nike.com/offer');

        $this->assertDatabaseCount('click_events', 1);
        $this->assertEquals(1, $coupon->fresh()->used_count);
    }

    public function test_go_link_falls_back_to_store_affiliate_url(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $store = $this->store();
        $coupon = Coupon::create(['store_id' => $store->id, 'title' => 'No url', 'status' => 'published']);

        $this->get("/go/coupon/{$coupon->id}")->assertRedirect('https://nike.com/?aff=x');
    }

    public function test_coupons_and_deals_indexes_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $store = $this->store();
        Coupon::create(['store_id' => $store->id, 'title' => 'C', 'status' => 'published']);
        Deal::create(['store_id' => $store->id, 'title' => 'D', 'status' => 'published', 'sale_price' => 9]);

        $this->get('/coupons')->assertStatus(200)->assertSee('C');
        $this->get('/deals')->assertStatus(200)->assertSee('D');
    }
}
