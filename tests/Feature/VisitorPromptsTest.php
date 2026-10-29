<?php

namespace Tests\Feature;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use App\Models\Setting;
use App\Models\User;
use App\Services\PushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VisitorPromptsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::set('maintenance_mode', '0', 'bool');
        config(['cms.push.public_key' => 'BTestPublicKey']);
    }

    private function subscription(string $endpoint = 'https://push.example.com/abc'): array
    {
        return ['endpoint' => $endpoint, 'keys' => ['p256dh' => 'pkey', 'auth' => 'atoken'], 'contentEncoding' => 'aes128gcm'];
    }

    public function test_home_has_cookie_banner_and_push_card_by_default(): void
    {
        $this->get('/')->assertStatus(200)
            ->assertSee('id="vp-cookie"', false)
            ->assertSee('id="vp-push"', false)
            ->assertSee('Cookie settings');
    }

    public function test_both_can_be_switched_off(): void
    {
        Setting::set('cookie_banner_enabled', '0', 'bool');
        Setting::set('push_enabled', '0', 'bool');
        $this->get('/')->assertStatus(200)
            ->assertDontSee('id="vp-cookie"', false)
            ->assertDontSee('id="vp-push"', false);
    }

    public function test_push_card_hidden_without_keys(): void
    {
        config(['cms.push.public_key' => '']);
        $this->get('/')->assertDontSee('id="vp-push"', false);
    }

    public function test_tracking_code_waits_for_consent(): void
    {
        Setting::set('consent_scripts', '<script>window.tracked = 1</script>', 'text');
        $html = $this->get('/')->getContent();
        $this->assertMatchesRegularExpression('#<template id="vp-consent-scripts"><script>window.tracked = 1</script></template>#', $html);
    }

    public function test_subscribe_stores_once_and_unsubscribe_removes(): void
    {
        $this->postJson('/push/subscribe', $this->subscription())->assertOk();
        $this->postJson('/push/subscribe', $this->subscription())->assertOk();
        $this->assertSame(1, PushSubscription::count());

        $this->postJson('/push/unsubscribe', ['endpoint' => 'https://push.example.com/abc'])->assertOk();
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_bad_subscription_is_rejected(): void
    {
        $this->postJson('/push/subscribe', ['endpoint' => 'http://insecure.example.com'])->assertStatus(422);
        $this->assertSame(0, PushSubscription::count());
    }

    public function test_click_is_counted_and_redirects_to_the_page(): void
    {
        $m = PushMessage::create(['title' => 'T', 'body' => 'B', 'url' => 'https://gipsypockets.store/store/nike']);
        $this->get('/push/click/'.$m->id)
            ->assertRedirect('https://gipsypockets.store/store/nike?utm_source=push&utm_medium=notification&utm_campaign=msg-'.$m->id);
        $this->assertSame(1, $m->fresh()->click_count);
    }

    public function test_sending_with_no_subscribers_marks_message_sent(): void
    {
        $m = PushMessage::create(['title' => 'T', 'body' => 'B']);
        $this->assertSame(['sent' => 0, 'failed' => 0], app(PushSender::class)->send($m));
        $this->assertNotNull($m->fresh()->sent_at);
    }

    public function test_admin_push_screens_load(): void
    {
        $this->actingAs(User::factory()->create());
        $p = config('cms.filament_path', 'admin');
        $this->get("/{$p}/push-messages")->assertStatus(200)->assertSee('turned on deal alerts');
        $this->get("/{$p}/push-messages/create")->assertStatus(200);
        $this->get("/{$p}/manage-visitor-prompts")->assertStatus(200)->assertSee('Deal alerts card');
    }

    public function test_service_worker_file_exists(): void
    {
        $this->assertFileExists(public_path('sw.js'));
    }
}
