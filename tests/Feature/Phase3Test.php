<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductList;
use App\Models\ProductListItem;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase3Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    public function test_admin_product_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());
        $p = $this->path();
        foreach (['products', 'product-categories', 'product-lists'] as $r) {
            $this->get("/{$p}/{$r}")->assertStatus(200);
            $this->get("/{$p}/{$r}/create")->assertStatus(200);
        }
    }

    public function test_published_roundup_renders_with_ranked_items(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $list = ProductList::create(['title' => 'Best Laptops', 'slug' => 'best-laptops', 'status' => 'published']);
        $a = Product::create(['title' => 'MacBook Air', 'slug' => 'macbook-air', 'affiliate_url' => 'https://x.com/mac']);
        $b = Product::create(['title' => 'Dell XPS', 'slug' => 'dell-xps', 'affiliate_url' => 'https://x.com/dell']);
        ProductListItem::create(['product_list_id' => $list->id, 'product_id' => $a->id, 'rank' => 1, 'badge' => 'Best Overall']);
        ProductListItem::create(['product_list_id' => $list->id, 'product_id' => $b->id, 'rank' => 2]);

        $res = $this->get('/best-laptops')->assertStatus(200);
        $res->assertSee('Best Laptops');
        $res->assertSee('Best Overall');
        $res->assertSee('MacBook Air');
        $res->assertSee('Dell XPS');
    }

    public function test_draft_roundup_is_not_public(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        ProductList::create(['title' => 'Secret List', 'slug' => 'secret-list', 'status' => 'draft']);
        $this->get('/secret-list')->assertStatus(404);
    }

    public function test_best_index_lists_published_roundups(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        ProductList::create(['title' => 'Best Phones', 'slug' => 'best-phones', 'status' => 'published']);
        $this->get('/best')->assertStatus(200)->assertSee('Best Phones');
    }

    public function test_product_go_link_tracks_and_redirects(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $p = Product::create(['title' => 'Thing', 'slug' => 'thing', 'affiliate_url' => 'https://shop.com/thing']);
        $this->get("/go/product/{$p->id}")->assertStatus(302)->assertRedirect('https://shop.com/thing');
        $this->assertDatabaseCount('click_events', 1);
    }
}
