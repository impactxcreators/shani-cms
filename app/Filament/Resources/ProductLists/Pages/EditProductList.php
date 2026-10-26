<?php

namespace App\Filament\Resources\ProductLists\Pages;

use App\Filament\Resources\ProductLists\ProductListResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditProductList extends EditRecord
{
    protected static string $resource = ProductListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
