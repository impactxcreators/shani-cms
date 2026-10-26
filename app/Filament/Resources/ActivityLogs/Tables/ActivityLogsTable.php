<?php
namespace App\Filament\Resources\ActivityLogs\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ActivityLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->dateTime()->sortable()->label('When'),
            TextColumn::make('description')->badge()->label('Action'),
            TextColumn::make('subject_type')->label('Item')
                ->formatStateUsing(fn ($state) => $state ? class_basename($state) : '—'),
            TextColumn::make('subject_id')->label('ID'),
            TextColumn::make('causer.name')->label('By')->placeholder('System'),
        ])->defaultSort('created_at', 'desc');
    }
}
