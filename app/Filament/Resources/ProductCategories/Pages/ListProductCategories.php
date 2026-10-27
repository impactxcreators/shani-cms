<?php

namespace App\Filament\Resources\ProductCategories\Pages;

use App\Filament\Imports\ProductCategoryImporter;
use App\Filament\Resources\ProductCategories\ProductCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Actions\ImportAction;
use Filament\Resources\Pages\ListRecords;

class ListProductCategories extends ListRecords
{
    protected static string $resource = ProductCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ImportAction::make()->importer(ProductCategoryImporter::class)->label('Import CSV'),
            CreateAction::make(),
        ];
    }
}
