<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Post;
use App\Models\ProductList;
use App\Models\ProductListItem;
use App\Models\Product;
use App\Models\Redirect;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase6Test extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_xml_lists_published_content(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Page::create(['title' => 'About', 'slug' => 'about', 'status' => 'published']);
        $res = $this->get('/sitemap.xml')->assertStatus(200);
        $res->assertHeader('Content-Type', 'application/xml');
        $res->assertSee(url('/about'), false);
    }

    public function test_robots_disallows_during_maintenance(): void
    {
        Setting::set('maintenance_mode', '1', 'bool');
        $this->get('/robots.txt')->assertStatus(200)->assertSee('Disallow: /');
    }

    public function test_robots_allows_when_live(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->get('/robots.txt')->assertStatus(200)->assertSee('Sitemap:');
    }

    public function test_rss_feed_lists_posts(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Post::create(['title' => 'RSS Post', 'slug' => 'rss-post', 'status' => 'published', 'published_at' => now()]);
        $this->get('/rss.xml')->assertStatus(200)->assertSee('RSS Post');
    }

    public function test_homepage_has_organization_jsonld(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->get('/')->assertStatus(200)->assertSee('"@type":"Organization"', false);
    }

    public function test_roundup_has_itemlist_jsonld(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $list = ProductList::create(['title' => 'Best X', 'slug' => 'best-x', 'status' => 'published']);
        $prod = Product::create(['title' => 'Prod', 'slug' => 'prod']);
        ProductListItem::create(['product_list_id' => $list->id, 'product_id' => $prod->id, 'rank' => 1]);
        $this->get('/best-x')->assertStatus(200)->assertSee('"@type":"ItemList"', false);
    }

    public function test_slug_change_creates_301(): void
    {
        $page = Page::create(['title' => 'Old', 'slug' => 'old-slug', 'status' => 'published']);
        $page->update(['slug' => 'new-slug']);

        $this->assertDatabaseHas('redirects', ['source' => '/old-slug', 'destination' => '/new-slug', 'status_code' => 301]);
    }

    public function test_store_slug_change_creates_prefixed_301(): void
    {
        $store = \App\Models\Store::create(['name' => 'S', 'slug' => 'oldstore', 'status' => 'published']);
        $store->update(['slug' => 'newstore']);
        $this->assertDatabaseHas('redirects', ['source' => '/store/oldstore', 'destination' => '/store/newstore']);
    }
}
