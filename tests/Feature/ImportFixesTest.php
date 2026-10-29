<?php

namespace Tests\Feature;

use App\Filament\Imports\CategoryImporter;
use App\Filament\Imports\CouponImporter;
use App\Filament\Imports\ProductCategoryImporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ImportFixesTest extends TestCase
{
    use RefreshDatabase;

    private function import(string $importer): Import
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return Import::create(['file_name' => 't.csv', 'file_path' => 't.csv', 'importer' => $importer,
            'total_rows' => 1, 'user_id' => $user->id]);
    }

    public function test_category_import_treats_parent_id_zero_as_no_parent(): void
    {
        $import = $this->import(ProductCategoryImporter::class);
        $run = new ProductCategoryImporter($import, ['name' => 'name', 'parent_id' => 'parent_id'], []);

        $run(['name' => 'Meal delivery services', 'parent_id' => '0']);
        $run(['name' => 'Orphan', 'parent_id' => '999']);

        $this->assertNull(ProductCategory::where('name', 'Meal delivery services')->value('parent_id'));
        $this->assertNull(ProductCategory::where('name', 'Orphan')->value('parent_id'));
    }

    public function test_category_import_keeps_a_real_parent(): void
    {
        $parent = ProductCategory::create(['name' => 'Food', 'slug' => 'food']);
        $import = $this->import(ProductCategoryImporter::class);
        $run = new ProductCategoryImporter($import, ['name' => 'name', 'parent_id' => 'parent_id'], []);

        $run(['name' => 'Snacks', 'parent_id' => (string) $parent->id]);

        $this->assertSame($parent->id, ProductCategory::where('name', 'Snacks')->value('parent_id'));
    }

    public function test_product_import_accepts_a_rating_out_of_ten(): void
    {
        $import = $this->import(ProductImporter::class);
        $run = new ProductImporter($import, ['title' => 'title', 'rating' => 'rating'], []);

        $run(['title' => 'Box Co', 'rating' => '8.5']);

        $this->assertEquals(8.5, (float) Product::where('title', 'Box Co')->value('rating'));
    }

    public function test_bad_optional_values_are_left_empty_and_the_rest_is_imported(): void
    {
        $import = $this->import(ProductImporter::class);
        $map = ['title' => 'title', 'brand' => 'brand', 'rating' => 'rating', 'review_count' => 'reviews',
            'price' => 'price', 'product_category_id' => 'cat'];
        $run = new ProductImporter($import, $map, []);

        $run(['title' => 'Box Co', 'brand' => 'BoxBrand', 'rating' => '15', 'reviews' => 'lots', 'price' => 'free', 'cat' => '0']);

        $p = Product::where('title', 'Box Co')->firstOrFail();
        $this->assertSame('BoxBrand', $p->brand);
        $this->assertNull($p->rating);
        $this->assertNull($p->review_count);
        $this->assertNull($p->product_category_id);
    }

    public function test_csv_with_only_a_title_column_still_imports(): void
    {
        $import = $this->import(ProductImporter::class);
        $run = new ProductImporter($import, ['title' => 'title'], []);

        $run(['title' => 'Just a title']);

        $this->assertDatabaseHas('products', ['title' => 'Just a title']);
    }

    public function test_store_category_import_ignores_unknown_parent(): void
    {
        $import = $this->import(CategoryImporter::class);
        $run = new CategoryImporter($import, ['name' => 'name', 'parent_id' => 'parent_id'], []);

        $run(['name' => 'Fashion', 'parent_id' => '0']);

        $this->assertNull(Category::where('name', 'Fashion')->value('parent_id'));
    }

    public function test_missing_required_field_still_rejects_the_row(): void
    {
        $import = $this->import(CouponImporter::class);
        $run = new CouponImporter($import, ['store_id' => 'store_id', 'title' => 'title'], []);

        $this->expectException(ValidationException::class);
        $run(['store_id' => '999', 'title' => '10% off']);
    }
}
