<?php

namespace App\Filament\Resources\ProductLists\Pages;

use App\Filament\Resources\ProductLists\ProductListResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListProductLists extends ListRecords
{
    protected static string $resource = ProductListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
