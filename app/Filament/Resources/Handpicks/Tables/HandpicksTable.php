<?php

namespace App\Filament\Resources\Handpicks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HandpicksTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable()->weight('bold'),
            TextColumn::make('category.name')->label('Category')->placeholder('—'),
            IconColumn::make('is_active')->boolean()->label('Active'),
            TextColumn::make('sort_order')->label('Order')->sortable(),
        ])->defaultSort('sort_order')->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
