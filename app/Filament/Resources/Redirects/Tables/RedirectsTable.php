<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('source')->searchable()->sortable()->weight('bold'),
                TextColumn::make('destination')->searchable()->color('gray'),
                TextColumn::make('status_code')->label('Type')->badge(),
                IconColumn::make('is_active')->boolean()->label('Active'),
                TextColumn::make('hit_count')->label('Hits')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->since()->label('Last used'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
