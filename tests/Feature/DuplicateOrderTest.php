<?php

namespace Tests\Feature;

use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DuplicateOrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicated_product_appears_right_below_the_original(): void
    {
        $this->actingAs(User::factory()->create());

        // same timestamp for all, like a CSV import
        $products = collect(range(1, 8))->map(fn ($i) => Product::create(['title' => "Product {$i}"]));
        Product::query()->update(['updated_at' => now()->subDay()]);
        $third = $products[2]->fresh();

        Livewire::test(ListProducts::class)->callTableAction('duplicate', $third)->assertHasNoTableActionErrors();

        $copy = Product::where('title', 'Product 3 (copy)')->firstOrFail();
        $this->assertSame($third->id, $copy->copied_from_id);
        $this->assertSame('draft', $copy->status ?? 'draft');

        $order = Livewire::test(ListProducts::class)->instance()->getTableRecords()->pluck('id')->all();
        $this->assertSame($copy->id, $order[array_search($third->id, $order) + 1]);

        // a copy of the copy also stays in the same group, below the original
        Livewire::test(ListProducts::class)->callTableAction('duplicate', $copy);
        $order = Livewire::test(ListProducts::class)->instance()->getTableRecords()->pluck('id')->all();
        $copy2 = Product::where('title', 'Product 3 (copy) (copy)')->firstOrFail();
        $at = array_search($third->id, $order);
        $this->assertSame([$third->id, $copy->id, $copy2->id], array_slice($order, $at, 3));
    }

    public function test_duplicated_coupon_appears_right_below_the_original(): void
    {
        $this->actingAs(User::factory()->create());
        $store = Store::create(['name' => 'S', 'slug' => 's', 'status' => 'published']);
        $coupons = collect(range(1, 8))->map(fn ($i) => Coupon::create(['store_id' => $store->id, 'title' => "Coupon {$i}", 'status' => 'published']));

        Livewire::test(ListCoupons::class)->callTableAction('duplicate', $coupons[1]);

        $copy = Coupon::where('title', 'Coupon 2 (copy)')->firstOrFail();
        $order = Livewire::test(ListCoupons::class)->instance()->getTableRecords()->pluck('id')->all();
        $this->assertSame($copy->id, $order[array_search($coupons[1]->id, $order) + 1]);
    }
}
