<?php

namespace App\Filament\Resources\ProductLists\Pages;

use App\Filament\Resources\ProductLists\ProductListResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateProductList extends CreateRecord
{
    protected static string $resource = ProductListResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = Auth::id();
        return $data;
    }
}
