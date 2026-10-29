<?php

namespace App\Filament\Resources\PushMessages\Pages;

use App\Filament\Resources\PushMessages\PushMessageResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePushMessage extends CreateRecord
{
    protected static string $resource = PushMessageResource::class;

    protected static ?string $title = 'New push campaign';

    // opens the edit screen, which has "Send test to me" and "Send now"
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Campaign saved — send a test, then send it';
    }
}
