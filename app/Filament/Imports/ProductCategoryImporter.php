<?php

namespace App\Filament\Imports;

use App\Models\ProductCategory;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class ProductCategoryImporter extends Importer
{
    protected static ?string $model = ProductCategory::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('slug')->rules(['max:255']),
            ImportColumn::make('description'),
            // Reference the parent by its name OR slug (friendlier than a database id).
            ImportColumn::make('parent')
                ->label('Parent category (name or slug)')
                ->fillRecordUsing(function (): void {
                    // handled in beforeSave; do not set on the model directly
                }),
            ImportColumn::make('parent_id')->numeric()->rules(['integer']),
            ImportColumn::make('icon')->rules(['max:255']),
            ImportColumn::make('sort_order')->numeric()->rules(['integer']),
            ImportColumn::make('is_featured')->boolean()->rules(['boolean']),
        ];
    }

    public function resolveRecord(): ProductCategory
    {
        if (filled($this->data['slug'] ?? null)) {
            return ProductCategory::firstOrNew(['slug' => $this->data['slug']]);
        }

        return ProductCategory::firstOrNew(['name' => $this->data['name']]);
    }

    protected function beforeSave(): void
    {
        $parent = $this->data['parent'] ?? null;

        if (filled($parent)) {
            $found = ProductCategory::where('slug', $parent)
                ->orWhere('name', $parent)
                ->first();

            if ($found && $found->id !== $this->record->id) {
                $this->record->parent_id = $found->id;
            }
        }
    }

    public static function getCompletedNotificationBody(\Filament\Actions\Imports\Models\Import $import): string
    {
        $body = \Illuminate\Support\Number::format($import->successful_rows) . ' ' . str('row')->plural($import->successful_rows) . ' imported.';
        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' ' . \Illuminate\Support\Number::format($failedRowsCount) . ' ' . str('row')->plural($failedRowsCount) . ' failed to import.';
        }

        return $body;
    }
}
