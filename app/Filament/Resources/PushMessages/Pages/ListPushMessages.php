<?php

namespace App\Filament\Resources\PushMessages\Pages;

use App\Filament\Resources\PushMessages\PushMessageResource;
use App\Models\PushSubscription;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListPushMessages extends ListRecords
{
    protected static string $resource = PushMessageResource::class;

    public function getSubheading(): ?string
    {
        return PushSubscription::count().' visitor(s) have turned on deal alerts. Create a campaign, pick what to promote, test it, then send.';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \App\Filament\Resources\PushMessages\Widgets\PushStats::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
