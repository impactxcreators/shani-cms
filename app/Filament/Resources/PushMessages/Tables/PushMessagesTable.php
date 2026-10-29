<?php

namespace App\Filament\Resources\PushMessages\Tables;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use App\Services\PushSender;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PushMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->weight('bold')->wrap(),
                TextColumn::make('sent_at')->label('Sent')->since()->placeholder('Not sent yet')->sortable(),
                TextColumn::make('sent_count')->label('Delivered')->numeric(),
                TextColumn::make('click_count')->label('Clicks')->numeric(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                Action::make('send')
                    ->label('Send now')
                    ->icon(Heroicon::OutlinedPaperAirplane)
                    ->color('success')
                    ->visible(fn (PushMessage $record): bool => $record->sent_at === null)
                    ->requiresConfirmation()
                    ->modalHeading('Send this notification?')
                    ->modalDescription(fn (): string => 'It goes to all '.PushSubscription::count().' subscriber(s) right away and cannot be taken back.')
                    ->modalSubmitActionLabel('Send')
                    ->action(function (PushMessage $record): void {
                        $result = app(PushSender::class)->send($record);
                        Notification::make()
                            ->title('Sent to '.$result['sent'].' subscriber(s)'.($result['failed'] ? ' ('.$result['failed'].' failed)' : ''))
                            ->success()
                            ->send();
                    }),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
