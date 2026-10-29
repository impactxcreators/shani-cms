<?php

namespace Tests\Feature;

use App\Filament\Resources\PushMessages\Pages\CreatePushMessage;
use App\Filament\Resources\PushMessages\Pages\EditPushMessage;
use App\Filament\Resources\PushMessages\Pages\ListPushMessages;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Post;
use App\Models\PushMessage;
use App\Models\Store;
use App\Models\User;
use App\Services\PushSender;
use App\Support\PushTargets;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PushCampaignTest extends TestCase
{
    use RefreshDatabase;

    private function store(): Store
    {
        return Store::create(['name' => 'Nike', 'slug' => 'nike', 'status' => 'published']);
    }

    public function test_suggestions_for_each_kind_of_content(): void
    {
        $store = $this->store();
        $coupon = Coupon::create(['store_id' => $store->id, 'title' => '20% off everything', 'code' => 'SAVE20', 'status' => 'published']);
        $deal = Deal::create(['store_id' => $store->id, 'title' => 'Air Max', 'sale_price' => 89, 'original_price' => 120, 'currency' => 'USD', 'status' => 'published']);
        $post = Post::create(['title' => 'Best running shoes', 'slug' => 'best-running-shoes', 'excerpt' => '<p>Our picks</p>', 'body' => 'x', 'status' => 'published']);

        $s = PushTargets::suggest('store', $store->id);
        $this->assertSame('New Nike coupons & deals', $s['title']);
        $this->assertSame(url('/store/nike'), $s['url']);

        $c = PushTargets::suggest('coupon', $coupon->id);
        $this->assertSame('Nike: 20% off everything', $c['title']);
        $this->assertStringContainsString('Use code SAVE20', $c['body']);

        $d = PushTargets::suggest('deal', $deal->id);
        $this->assertStringContainsString('Now $89 (was $120)', $d['body']);

        $p = PushTargets::suggest('post', $post->id);
        $this->assertSame('Our picks', $p['body']);
        $this->assertSame(url('/blog/best-running-shoes'), $p['url']);

        $this->assertNull(PushTargets::suggest('link', 1));
        $this->assertSame([$coupon->id => '20% off everything — Nike'], PushTargets::search('coupon', '20%'));
    }

    public function test_picking_an_item_fills_in_the_notification(): void
    {
        $this->actingAs(User::factory()->create());
        $store = $this->store();

        Livewire::test(CreatePushMessage::class)
            ->fillForm(['promote_type' => 'store'])
            ->fillForm(['promote_id' => $store->id])
            ->assertSchemaStateSet(['title' => 'New Nike coupons & deals', 'url' => url('/store/nike')])
            ->call('create')
            ->assertHasNoFormErrors();

        $m = PushMessage::firstOrFail();
        $this->assertSame('store', $m->promote_type);
        $this->assertSame($store->id, (int) $m->promote_id);
    }

    public function test_campaign_screens_load(): void
    {
        $this->actingAs(User::factory()->create());
        $m = PushMessage::create(['title' => 'T', 'body' => 'B']);
        Livewire::test(ListPushMessages::class)->assertOk()->assertCanSeeTableRecords([$m]);
        Livewire::test(EditPushMessage::class, ['record' => $m->id])->assertOk()->assertSee('Preview');
    }

    public function test_a_campaign_can_only_be_sent_once(): void
    {
        $m = PushMessage::create(['title' => 'T', 'body' => 'B']);
        $this->assertSame(['sent' => 0, 'failed' => 0], app(PushSender::class)->send($m));
        $this->assertNull(app(PushSender::class)->send($m->fresh()));
    }

    public function test_scheduled_campaigns_go_out_when_due(): void
    {
        $due = PushMessage::create(['title' => 'Due', 'body' => 'B', 'scheduled_at' => now()->subMinute()]);
        $later = PushMessage::create(['title' => 'Later', 'body' => 'B', 'scheduled_at' => now()->addHour()]);

        $this->artisan('push:send-scheduled')->assertSuccessful();

        $this->assertNotNull($due->fresh()->sent_at);
        $this->assertNull($later->fresh()->sent_at);
    }

    public function test_send_test_without_own_subscription_explains_what_to_do(): void
    {
        $this->actingAs(User::factory()->create());
        $m = PushMessage::create(['title' => 'T', 'body' => 'B']);

        Livewire::test(ListPushMessages::class)
            ->callTableAction('sendTest', $m)
            ->assertNotified('Turn on deal alerts for yourself first');
        $this->assertNull($m->fresh()->sent_at);
    }

    public function test_logged_in_admin_subscription_is_linked_for_tests(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->postJson('/push/subscribe', [
            'endpoint' => 'https://push.example.com/me', 'keys' => ['p256dh' => 'k', 'auth' => 'a'],
        ])->assertOk();
        $this->assertDatabaseHas('push_subscriptions', ['user_id' => $user->id]);
    }

    public function test_duplicating_a_sent_campaign_gives_a_fresh_draft_with_clean_title(): void
    {
        $this->actingAs(User::factory()->create());
        $m = PushMessage::create(['name' => 'Nike promo', 'title' => 'Nike sale', 'body' => 'B',
            'sent_at' => now(), 'sent_count' => 50, 'click_count' => 7]);

        Livewire::test(ListPushMessages::class)->callTableAction('duplicate', $m);

        $copy = PushMessage::whereKeyNot($m->id)->firstOrFail();
        $this->assertSame('Nike sale', $copy->title);
        $this->assertSame('Nike promo (copy)', $copy->name);
        $this->assertNull($copy->sent_at);
        $this->assertSame(0, (int) $copy->sent_count);
        $this->assertSame(0, (int) $copy->click_count);
    }
}
