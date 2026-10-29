<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageVisitorPrompts;
use App\Filament\Resources\PushMessages\Pages\ListPushMessages;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class VisitorPromptSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('maintenance_mode', '0', 'bool');
        config(['cms.push.public_key' => 'BTestPublicKey']);
    }

    public function test_settings_page_saves_and_site_uses_them(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ManageVisitorPrompts::class)
            ->fillForm([
                'cookie_banner_delay' => '5',
                'cookie_banner_position' => 'bar',
                'cookie_accept_label' => 'Sounds good',
                'cookie_show_reject' => false,
                'push_prompt_delay' => '45',
                'push_prompt_scroll' => '40',
                'push_prompt_position' => 'left',
                'push_yes_label' => 'Notify me',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $html = $this->get('/')->assertOk()->getContent();
        $this->assertStringContainsString('class="vp vp-bar"', $html);
        $this->assertStringContainsString('Sounds good', $html);
        $this->assertStringNotContainsString('data-consent="essential"', $html);
        $this->assertStringContainsString('vp-push vp-left', $html);
        $this->assertStringContainsString('Notify me', $html);
        $this->assertStringContainsString('"cookieDelay":5000', $html);
        $this->assertStringContainsString('"pushDelay":45000', $html);
        $this->assertStringContainsString('"scroll":40', $html);
    }

    public function test_push_card_does_not_pop_up_on_excluded_pages(): void
    {
        Setting::set('push_exclude_paths', "/privacy-policy\n/blog/*", 'text');
        \App\Models\Page::create(['title' => 'Privacy', 'slug' => 'privacy-policy', 'status' => 'published', 'body' => 'x']);

        $this->assertStringContainsString('data-auto="0"', $this->get('/privacy-policy')->getContent());
        $this->assertStringContainsString('data-auto="1"', $this->get('/')->getContent());
    }

    public function test_footer_deal_alerts_link_can_be_hidden(): void
    {
        $this->assertStringContainsString('<span data-push-manage', $this->get('/')->getContent());
        Setting::set('push_footer_link', '0', 'bool');
        $this->assertStringNotContainsString('<span data-push-manage', $this->get('/')->getContent());
    }

    public function test_push_campaigns_screen_shows_subscriber_numbers(): void
    {
        $this->actingAs(User::factory()->create());
        foreach (['a', 'b', 'c'] as $i => $e) {
            PushSubscription::create(['endpoint_hash' => hash('sha256', $e), 'endpoint' => 'https://p.example/'.$e,
                'public_key' => 'k', 'auth_token' => 'a', 'user_agent' => $i ? 'Mozilla (Windows)' : 'Mozilla (iPhone; Mobile)']);
        }

        Livewire::test(\App\Filament\Resources\PushMessages\Widgets\PushStats::class)
            ->assertSee('Subscribers')
            ->assertSee('+3 in the last 7 days')
            ->assertSee('1 / 2');
    }
}
