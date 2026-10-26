<?php

namespace App\Filament\Resources\Categories\Pages;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Imports\CategoryImporter;
use Filament\Actions\ImportAction;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCategories extends ListRecords
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(CategoryImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
