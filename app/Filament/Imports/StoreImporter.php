<?php

namespace App\Filament\Imports;

use App\Models\Store;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class StoreImporter extends Importer
{
    protected static ?string $model = Store::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('name')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('slug')->rules(['max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('logo')->rules(['max:1000']),
            ImportColumn::make('website_url')->rules(['max:1000']),
            ImportColumn::make('affiliate_url')->rules(['max:1000']),
            ImportColumn::make('is_active')->boolean()->rules(['boolean']),
            ImportColumn::make('is_featured')->boolean()->rules(['boolean']),
            ImportColumn::make('average_rating')->numeric()->rules(['numeric', 'between:0,5']),
            ImportColumn::make('status')->rules(['max:50']),
            ImportColumn::make('meta_title')->rules(['max:255']),
            ImportColumn::make('meta_description')->rules(['max:500']),
        ];
    }

    public function resolveRecord(): Store
    {
        if (filled($this->data['slug'] ?? null)) {
            return Store::firstOrNew(['slug' => $this->data['slug']]);
        }

        return Store::firstOrNew(['name' => $this->data['name']]);
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->status)) {
            $this->record->status = 'published';
        }
        if ($this->record->is_active === null) {
            $this->record->is_active = true;
        }
        if ($this->record->status === 'published' && blank($this->record->published_at)) {
            $this->record->published_at = now();
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
