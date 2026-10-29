<?php

namespace App\Filament\Resources\PushMessages\Pages;

use App\Filament\Resources\PushMessages\PushMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePushMessage extends CreateRecord
{
    protected static string $resource = PushMessageResource::class;

    // back to the list, where the "Send now" button is
    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('index');
    }
}
