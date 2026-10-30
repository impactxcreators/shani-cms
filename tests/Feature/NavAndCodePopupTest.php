<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavAndCodePopupTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('maintenance_mode', '0', 'bool');
    }

    public function test_header_has_categories_menu_with_subcategories(): void
    {
        $fashion = Category::create(['name' => 'Fashion']);
        Category::create(['name' => 'Shoes', 'parent_id' => $fashion->id]);

        $res = $this->get('/coupons')->assertOk();
        $res->assertSee('data-navdrop', false);
        $res->assertSeeInOrder([route('category.show', 'fashion'), route('category.show', 'shoes'), 'Browse all categories'], false);

        // New categories show up straight away (the menu cache is cleared on save).
        Category::create(['name' => 'Travel']);
        $this->get('/coupons')->assertSee(route('category.show', 'travel'), false);
    }

    public function test_show_code_buttons_carry_popup_data(): void
    {
        $store = Store::create(['name' => 'Nike', 'slug' => 'nike', 'status' => 'published', 'is_active' => true]);
        Coupon::create(['store_id' => $store->id, 'title' => '20% off shoes', 'code' => 'RUN20', 'status' => 'published']);

        $this->get('/coupons')->assertOk()
            ->assertSee('id="codemodal"', false)
            ->assertSee('data-reveal data-code="RUN20" data-store="Nike" data-title="20% off shoes"', false);

        $this->get('/store/nike')->assertOk()
            ->assertSee('data-reveal data-code="RUN20" data-store="Nike"', false)
            ->assertDontSee('onclick=', false);
    }
}
