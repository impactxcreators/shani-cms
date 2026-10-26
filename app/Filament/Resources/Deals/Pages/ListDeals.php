<?php

namespace App\Filament\Resources\Deals\Pages;

use App\Filament\Resources\Deals\DealResource;
use App\Filament\Imports\DealImporter;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDeals extends ListRecords
{
    protected static string $resource = DealResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(DealImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
