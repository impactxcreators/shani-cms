<?php

namespace App\Filament\Resources\Handpicks\Pages;

use App\Filament\Resources\Handpicks\HandpickResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditHandpick extends EditRecord
{
    protected static string $resource = HandpickResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
