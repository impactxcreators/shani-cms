<?php

namespace App\Filament\Resources\PushMessages\Pages;

use App\Filament\Resources\PushMessages\PushMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPushMessage extends EditRecord
{
    protected static string $resource = PushMessageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
