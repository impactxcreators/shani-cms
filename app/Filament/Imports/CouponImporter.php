<?php

namespace App\Filament\Imports;

use App\Models\Coupon;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class CouponImporter extends Importer
{
    use Concerns\SkipsInvalidFields;

    protected static ?string $model = Coupon::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('store_id')->requiredMapping()->numeric()->rules(['required', 'integer', 'exists:stores,id']),
            ImportColumn::make('title')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('code')->rules(['max:255']),
            ImportColumn::make('description'),
            ImportColumn::make('discount_type')->rules(['max:50']),
            ImportColumn::make('discount_value')->numeric()->rules(['numeric']),
            ImportColumn::make('destination_url')->rules(['max:1000']),
            ImportColumn::make('expires_at')->rules(['date']),
            ImportColumn::make('is_exclusive')->boolean()->rules(['boolean']),
            ImportColumn::make('is_verified')->boolean()->rules(['boolean']),
            ImportColumn::make('status')->rules(['max:50']),
            ImportColumn::make('sort_order')->numeric()->rules(['integer']),
        ];
    }

    public function resolveRecord(): Coupon
    {
        if (filled($this->data['code'] ?? null) && filled($this->data['store_id'] ?? null)) {
            return Coupon::firstOrNew([
                'store_id' => $this->data['store_id'],
                'code' => $this->data['code'],
            ]);
        }

        return new Coupon();
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->status)) {
            $this->record->status = 'published';
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
