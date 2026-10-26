<?php

namespace App\Filament\Imports;

use App\Models\Deal;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class DealImporter extends Importer
{
    protected static ?string $model = Deal::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('store_id')->requiredMapping()->numeric()->rules(['required', 'integer']),
            ImportColumn::make('title')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('original_price')->numeric()->rules(['numeric']),
            ImportColumn::make('sale_price')->numeric()->rules(['numeric']),
            ImportColumn::make('currency')->rules(['max:3']),
            ImportColumn::make('destination_url')->rules(['max:1000']),
            ImportColumn::make('expires_at')->rules(['date']),
            ImportColumn::make('is_featured')->boolean()->rules(['boolean']),
            ImportColumn::make('status')->rules(['max:50']),
            ImportColumn::make('sort_order')->numeric()->rules(['integer']),
        ];
    }

    public function resolveRecord(): Deal
    {
        if (filled($this->data['title'] ?? null) && filled($this->data['store_id'] ?? null)) {
            return Deal::firstOrNew([
                'store_id' => $this->data['store_id'],
                'title' => $this->data['title'],
            ]);
        }

        return new Deal();
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->status)) {
            $this->record->status = 'published';
        }
        if (blank($this->record->currency)) {
            $this->record->currency = 'USD';
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
