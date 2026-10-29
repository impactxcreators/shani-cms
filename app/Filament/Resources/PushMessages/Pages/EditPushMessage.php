<?php

namespace App\Filament\Resources\PushMessages\Pages;

use App\Filament\Resources\PushMessages\PushActions;
use App\Filament\Resources\PushMessages\PushMessageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditPushMessage extends EditRecord
{
    protected static string $resource = PushMessageResource::class;

    public function getSubheading(): ?string
    {
        $r = $this->getRecord();

        if ($r->sent_at) {
            return 'Sent '.$r->sent_at->diffForHumans().' to '.$r->sent_count.' subscriber(s) · '.$r->click_count.' click(s).';
        }

        return $r->scheduled_at
            ? 'Scheduled for '.$r->scheduled_at->format('M j, Y H:i').'.'
            : 'Draft. Try "Send test to me" first, then "Send now".';
    }

    protected function getHeaderActions(): array
    {
        return [
            PushActions::sendTest(),
            PushActions::sendNow(),
            DeleteAction::make(),
        ];
    }
}
