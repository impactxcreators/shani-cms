<?php

namespace App\Filament\Resources\PushMessages;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use App\Services\PushSender;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

/** "Send test to me" and "Send now", shared by the campaign list and the edit screen. */
class PushActions
{
    public static function sendTest(): Action
    {
        return Action::make('sendTest')
            ->label('Send test to me')
            ->icon(Heroicon::OutlinedBeaker)
            ->color('gray')
            ->visible(fn (PushMessage $record): bool => $record->sent_at === null)
            ->action(function (PushMessage $record, $livewire): void {
                self::saveFirst($livewire, $record);

                if (! PushSubscription::where('user_id', auth()->id())->exists()) {
                    Notification::make()
                        ->title('Turn on deal alerts for yourself first')
                        ->body('While logged in, open the website in this browser, click "Deal alerts" in the footer and allow notifications. Then press "Send test to me" again.')
                        ->warning()->persistent()->send();

                    return;
                }

                $r = app(PushSender::class)->sendTest($record, auth()->id());
                Notification::make()
                    ->title($r['sent'] ? 'Test sent — check your notifications' : 'Test could not be delivered')
                    ->{$r['sent'] ? 'success' : 'danger'}()->send();
            });
    }

    public static function sendNow(): Action
    {
        return Action::make('send')
            ->label('Send now')
            ->icon(Heroicon::OutlinedPaperAirplane)
            ->color('success')
            ->visible(fn (PushMessage $record): bool => $record->sent_at === null)
            ->requiresConfirmation()
            ->modalHeading('Send this campaign now?')
            ->modalDescription(fn (): string => 'It goes to all '.PushSubscription::count().' subscriber(s) right away and cannot be taken back.')
            ->modalSubmitActionLabel('Send to everyone')
            ->action(function (PushMessage $record, $livewire): void {
                self::saveFirst($livewire, $record);

                $r = app(PushSender::class)->send($record);
                if ($r === null) {
                    Notification::make()->title('This campaign was already sent')->warning()->send();

                    return;
                }
                Notification::make()
                    ->title('Sent to '.$r['sent'].' subscriber(s)'.($r['failed'] ? ' ('.$r['failed'].' failed)' : ''))
                    ->success()->send();
            });
    }

    /** on the edit screen, send what is in the form, not the last saved version */
    private static function saveFirst($livewire, PushMessage $record): void
    {
        if ($livewire instanceof \Filament\Resources\Pages\EditRecord) {
            $livewire->save(shouldRedirect: false, shouldSendSavedNotification: false);
            $record->refresh();
        }
    }
}
