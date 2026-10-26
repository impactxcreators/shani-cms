<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Imports\PageImporter;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(PageImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
