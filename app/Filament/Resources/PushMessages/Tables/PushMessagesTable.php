<?php

namespace App\Filament\Resources\PushMessages\Tables;

use App\Filament\Resources\PushMessages\PushActions;
use App\Filament\Support\Duplicate;
use App\Models\PushMessage;
use App\Support\PushTargets;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PushMessagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('Campaign')
                    ->description(fn (PushMessage $record): ?string => $record->name)
                    ->searchable(['title', 'name'])
                    ->weight('bold')
                    ->wrap(),
                TextColumn::make('promote_type')
                    ->label('Promotes')
                    ->formatStateUsing(fn (?string $state): string => PushTargets::label($state))
                    ->badge()
                    ->color('gray'),
                TextColumn::make('status')
                    ->state(fn (PushMessage $record): string => $record->sent_at ? 'Sent' : ($record->scheduled_at ? 'Scheduled' : 'Draft'))
                    ->badge()
                    ->color(fn (string $state): string => ['Sent' => 'success', 'Scheduled' => 'warning'][$state] ?? 'gray')
                    ->description(fn (PushMessage $record): ?string => $record->sent_at
                        ? $record->sent_at->diffForHumans()
                        : $record->scheduled_at?->format('M j, H:i')),
                TextColumn::make('sent_count')->label('Delivered')->numeric(),
                TextColumn::make('click_count')->label('Clicks')->numeric(),
                TextColumn::make('ctr')
                    ->label('Click rate')
                    ->state(fn (PushMessage $record): string => $record->sent_count ? round($record->click_count / $record->sent_count * 100, 1).'%' : '—'),
            ])
            ->defaultSort(Duplicate::sort('created_at', 'desc'))
            ->recordActions([
                PushActions::sendTest(),
                PushActions::sendNow(),
                EditAction::make(),
                Duplicate::make()->successNotificationTitle('Copied — edit it, then send'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
