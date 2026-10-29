<?php

namespace App\Filament\Resources\RefererLinks\Pages;

use App\Filament\Resources\RefererLinks\RefererLinkResource;
use App\Models\RefererLink;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateRefererLink extends CreateRecord
{
    protected static string $resource = RefererLinkResource::class;

    protected static ?string $title = 'Add Referer Link';

    protected function afterCreate(): void
    {
        foreach ($this->record->destinations as $d) {
            $others = RefererLink::existingFor($d->url, $this->record->id);
            if ($others->isNotEmpty()) {
                Notification::make()
                    ->title('Created — but this destination already had a short link')
                    ->body($d->url.' is also used by '.$others->map->short_url->implode(', '))
                    ->warning()->persistent()->send();
            }
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Short link created: '.$this->record->short_url;
    }
}
