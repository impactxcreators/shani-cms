<?php

namespace Tests\Feature;

use App\Filament\Imports\ProductCategoryImporter;
use App\Filament\Imports\ProductImporter;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
