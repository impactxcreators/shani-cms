<?php

namespace Tests\Feature;

use App\Models\Advert;
use App\Models\HomeBlock;
use App\Models\Post;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase5Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    public function test_admin_home_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());
        $p = $this->path();
        foreach (['home-banners', 'home-blocks', 'adverts'] as $r) {
            $this->get("/{$p}/{$r}")->assertStatus(200);
            $this->get("/{$p}/{$r}/create")->assertStatus(200);
        }
    }

    public function test_homepage_renders_blocks(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Post::create(['title' => 'Homepage Post', 'slug' => 'homepage-post', 'status' => 'published',
            'published_at' => now(), 'excerpt' => 'hi']);
        HomeBlock::create(['type' => 'blog_feed', 'title' => 'Fresh Reads', 'config' => ['limit' => 4],
            'is_active' => true, 'sort_order' => 1]);

        $this->get('/')->assertStatus(200)->assertSee('Fresh Reads')->assertSee('Homepage Post');
    }

    public function test_inactive_block_is_hidden(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        HomeBlock::create(['type' => 'rich_text', 'title' => 'Hidden Block',
            'config' => ['text' => 'secret'], 'is_active' => false, 'sort_order' => 1]);
        $this->get('/')->assertStatus(200)->assertDontSee('Hidden Block');
    }

    public function test_default_homepage_when_no_blocks(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Setting::set('tagline', 'Deals galore', 'string');
        $this->get('/')->assertStatus(200)->assertSee('Deals galore');
    }

    public function test_advert_click_tracks_and_redirects(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $ad = Advert::create(['placement' => 'home_top', 'destination_url' => 'https://promo.example', 'is_active' => true]);
        $this->get("/ad/{$ad->id}")->assertStatus(302)->assertRedirect('https://promo.example');
        $this->assertEquals(1, $ad->fresh()->clicks);
    }
}
