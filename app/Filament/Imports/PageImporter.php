<?php

namespace App\Filament\Imports;

use App\Models\Page;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;

class PageImporter extends Importer
{
    use Concerns\SkipsInvalidFields;

    protected static ?string $model = Page::class;

    public static function getColumns(): array
    {
        return [
            ImportColumn::make('title')->requiredMapping()->rules(['required', 'max:255']),
            ImportColumn::make('slug')->rules(['max:255']),
            ImportColumn::make('body'),
            ImportColumn::make('template')->rules(['max:100']),
            ImportColumn::make('show_in_header')->boolean()->rules(['boolean']),
            ImportColumn::make('show_in_footer')->boolean()->rules(['boolean']),
            ImportColumn::make('sort_order')->numeric()->rules(['integer']),
            ImportColumn::make('status')->rules(['max:50']),
            ImportColumn::make('meta_title')->rules(['max:255']),
            ImportColumn::make('meta_description')->rules(['max:500']),
        ];
    }

    public function resolveRecord(): Page
    {
        if (filled($this->data['slug'] ?? null)) {
            return Page::firstOrNew(['slug' => $this->data['slug']]);
        }

        return Page::firstOrNew(['title' => $this->data['title']]);
    }

    protected function beforeSave(): void
    {
        if (blank($this->record->status)) {
            $this->record->status = 'published';
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
