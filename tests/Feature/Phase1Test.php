<?php

namespace Tests\Feature;

use App\Models\Page;
use App\Models\Redirect;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase1Test extends TestCase
{
    use RefreshDatabase;

    private function adminPath(): string
    {
        return config('cms.filament_path', 'admin');
    }

    public function test_guests_see_coming_soon_when_maintenance_on(): void
    {
        Setting::set('maintenance_mode', '1', 'bool');
        $this->get('/')->assertStatus(503)->assertSee('Launching soon');
    }

    public function test_admin_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());

        $p = $this->adminPath();
        $this->get("/{$p}/pages")->assertStatus(200);
        $this->get("/{$p}/pages/create")->assertStatus(200);
        $this->get("/{$p}/redirects")->assertStatus(200);
        $this->get("/{$p}/redirects/create")->assertStatus(200);
        $this->get("/{$p}/manage-site-settings")->assertStatus(200);
    }

    public function test_published_page_renders_at_its_slug(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Page::create([
            'title' => 'About Us',
            'slug' => 'about-us',
            'body' => '<p>Hello world</p>',
            'status' => 'published',
        ]);

        $this->get('/about-us')->assertStatus(200)->assertSee('About Us');
    }

    public function test_draft_page_is_not_public(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Page::create(['title' => 'Secret', 'slug' => 'secret', 'status' => 'draft']);
        $this->get('/secret')->assertStatus(404);
    }

    public function test_redirect_sends_visitor_onward(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        Redirect::create([
            'source' => '/old-page',
            'destination' => '/about-us',
            'status_code' => 301,
            'is_active' => true,
        ]);

        $this->get('/old-page')->assertStatus(301)->assertRedirect('/about-us');
    }
}
