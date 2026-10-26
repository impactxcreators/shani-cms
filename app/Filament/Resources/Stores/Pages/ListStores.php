<?php

namespace App\Filament\Resources\Stores\Pages;

use App\Filament\Resources\Stores\StoreResource;
use App\Filament\Imports\StoreImporter;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListStores extends ListRecords
{
    protected static string $resource = StoreResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(StoreImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
