<?php

namespace App\Filament\Resources\Handpicks\Pages;

use App\Filament\Resources\Handpicks\HandpickResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListHandpicks extends ListRecords
{
    protected static string $resource = HandpickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
