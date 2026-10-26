<?php

namespace App\Filament\Imports;

use App\Models\Category;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class CategoryImporter extends Importer
{
    protected static ?string $model = Category::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('slug')->rules(['max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('parent_id')->numeric()->rules(['integer']),
            ImportColumn::make('icon')->rules(['max:255']),
            ImportColumn::make('sort_order')->numeric()->rules(['integer']),
            ImportColumn::make('is_featured')->boolean()->rules(['boolean']),
        ];
    }

    public function resolveRecord(): Category
    {
        if (filled($this->data['slug'] ?? null)) {
            return Category::firstOrNew(['slug' => $this->data['slug']]);
        }

        return Category::firstOrNew(['name' => $this->data['name']]);
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
