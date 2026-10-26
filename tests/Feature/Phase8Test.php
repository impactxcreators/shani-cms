<?php

namespace Tests\Feature;

use App\Models\ClickEvent;
use App\Models\Coupon;
use App\Models\Setting;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class Phase8Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    public function test_admin_settings_screens_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create(['is_active' => true]));
        $p = $this->path();
        $this->get("/{$p}/users")->assertStatus(200);
        $this->get("/{$p}/activity-logs")->assertStatus(200);
        $this->get("/{$p}/manage-site-settings")->assertStatus(200);
    }

    public function test_inactive_user_cannot_access_panel(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create(['is_active' => false]));
        $this->get('/'.$this->path())->assertStatus(403);
    }

    public function test_roles_can_be_assigned(): void
    {
        Role::create(['name' => 'Editor', 'guard_name' => 'web']);
        $user = User::factory()->create();
        $user->assignRole('Editor');
        $this->assertTrue($user->fresh()->hasRole('Editor'));
    }

    public function test_changes_are_logged_to_activity_log(): void
    {
        $store = Store::create(['name' => 'Logged Store', 'slug' => 'logged-store', 'status' => 'published']);
        $store->update(['name' => 'Renamed Store']);
        $this->assertDatabaseHas('activity_log', ['subject_type' => Store::class, 'subject_id' => $store->id]);
    }

    public function test_expired_coupons_are_archived_by_command(): void
    {
        $store = Store::create(['name' => 'S', 'slug' => 's', 'status' => 'published']);
        $c = Coupon::create(['store_id' => $store->id, 'title' => 'Old', 'status' => 'published', 'expires_at' => now()->subDay()]);
        $this->artisan('coupons:archive-expired')->assertSuccessful();
        $this->assertEquals('archived', $c->fresh()->status);
    }

    public function test_old_clicks_are_pruned(): void
    {
        ClickEvent::create(['entity_type' => 'coupon', 'entity_id' => 1, 'created_at' => now()->subDays(100)]);
        ClickEvent::create(['entity_type' => 'coupon', 'entity_id' => 2, 'created_at' => now()->subDays(5)]);
        $this->artisan('clicks:prune')->assertSuccessful();
        $this->assertEquals(1, ClickEvent::count());
    }

    public function test_database_backup_creates_a_file(): void
    {
        if (\Illuminate\Support\Facades\DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('db:backup uses MySQL-specific SQL; verified on the live MySQL database.');
        }
        Storage::fake('local');
        $this->artisan('db:backup')->assertSuccessful();
        $this->assertNotEmpty(Storage::disk('local')->files('backups'));
    }
}
