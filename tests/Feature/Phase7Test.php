<?php

namespace Tests\Feature;

use App\Filament\Imports\ProductImporter;
use App\Filament\Widgets\ClicksChart;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Phase7Test extends TestCase
{
    use RefreshDatabase;

    private function path(): string { return config('cms.filament_path', 'admin'); }

    public function test_dashboard_and_products_page_load(): void
    {
        Setting::set('maintenance_mode', '0', 'bool');
        $this->actingAs(User::factory()->create());
        $p = $this->path();
        $this->get("/{$p}")->assertStatus(200);         // dashboard with widgets
        $this->get("/{$p}/products")->assertStatus(200); // import action present
    }

    public function test_clicks_chart_returns_14_days(): void
    {
        $chart = new ClicksChart();
        $method = (new \ReflectionClass($chart))->getMethod('getData');
        $method->setAccessible(true);
        $data = $method->invoke($chart);
        $this->assertCount(14, $data['labels']);
        $this->assertCount(14, $data['datasets'][0]['data']);
    }

    public function test_csv_importer_creates_a_product_row(): void
    {
        $user = User::factory()->create();
        $import = new Import();
        $import->user_id = $user->id;
        $import->file_name = 'products.csv';
        $import->file_path = 'products.csv';
        $import->importer = ProductImporter::class;
        $import->total_rows = 1;
        $import->save();

        $columnMap = [
            'title' => 'title', 'slug' => 'slug', 'brand' => 'brand',
            'price' => 'price', 'currency' => 'currency',
        ];

        $importer = new ProductImporter($import, $columnMap, []);
        $importer(['title' => 'Imported Phone', 'slug' => 'imported-phone', 'brand' => 'Acme', 'price' => '199', 'currency' => 'USD']);

        $this->assertDatabaseHas('products', ['slug' => 'imported-phone', 'brand' => 'Acme']);
    }

    public function test_importer_upserts_on_slug(): void
    {
        $user = User::factory()->create();
        Product::create(['title' => 'Existing', 'slug' => 'dup-slug', 'brand' => 'Old']);

        $import = new Import();
        $import->user_id = $user->id;
        $import->file_name = 'p.csv'; $import->file_path = 'p.csv';
        $import->importer = ProductImporter::class; $import->total_rows = 1;
        $import->save();

        $importer = new ProductImporter($import, ['title' => 'title', 'slug' => 'slug', 'brand' => 'brand'], []);
        $importer(['title' => 'Existing', 'slug' => 'dup-slug', 'brand' => 'New']);

        $this->assertEquals(1, Product::where('slug', 'dup-slug')->count());
        $this->assertEquals('New', Product::where('slug', 'dup-slug')->first()->brand);
    }
}
