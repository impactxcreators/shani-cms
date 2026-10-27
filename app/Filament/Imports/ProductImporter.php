<?php

namespace App\Filament\Imports;

use App\Models\Product;
use App\Models\ProductCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Number;

class ProductImporter extends Importer
{
    protected static ?string $model = Product::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('slug')->rules(['max:255']),
            ImportColumn::make('brand')->rules(['max:255']),
            ImportColumn::make('image_url')->rules(['max:1000']),
            ImportColumn::make('description'),
            ImportColumn::make('highlights')
                ->castStateUsing(fn ($state) => filled($state) ? array_map('trim', explode('|', (string) $state)) : null),
            ImportColumn::make('rating')->numeric()->rules(['numeric', 'between:0,5']),
            ImportColumn::make('review_count')->numeric()->rules(['integer']),
            ImportColumn::make('price')->numeric()->rules(['numeric']),
            ImportColumn::make('currency')->rules(['max:3']),
            ImportColumn::make('external_id')->rules(['max:255']),
            ImportColumn::make('affiliate_url')->rules(['max:1000']),
            ImportColumn::make('is_available')->boolean()->rules(['boolean']),
            // Match a product category by NAME or slug (auto-creates it if missing).
            ImportColumn::make('product_category')
                ->label('Product category (name or slug)')
                ->fillRecordUsing(function (): void {
                    // handled in beforeSave; do not set on the model directly
                }),
            ImportColumn::make('product_category_id')->numeric()->rules(['integer']),
        ];
    }

    public function resolveRecord(): Product
    {
        if (filled($this->data['external_id'] ?? null)) {
            return Product::firstOrNew(['external_id' => $this->data['external_id']]);
        }
        if (filled($this->data['slug'] ?? null)) {
            return Product::firstOrNew(['slug' => $this->data['slug']]);
        }
        return new Product();
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->currency)) {
            $this->record->currency = 'USD';
        }
        if ($this->record->is_available === null) {
            $this->record->is_available = true;
        }

        // Resolve/auto-create the product category from a name or slug.
        $cat = $this->data['product_category'] ?? null;
        if (filled($cat)) {
            $found = ProductCategory::where('slug', $cat)
                ->orWhere('name', $cat)
                ->first();

            if (! $found) {
                $found = ProductCategory::create(['name' => $cat]);
            }

            $this->record->product_category_id = $found->id;
        }
    }

    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your product import has completed and ' . Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }
        return $body;
    }
}
