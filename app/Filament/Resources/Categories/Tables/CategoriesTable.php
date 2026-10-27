<?php

namespace App\Filament\Resources\Categories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('slug')->color('gray'),
            TextColumn::make('parent.name')->label('Parent')->placeholder('—'),
            TextColumn::make('stores_count')->counts('stores')->label('Stores'),
            IconColumn::make('is_featured')->boolean()->label('Featured'),
            TextColumn::make('sort_order')->sortable()->label('Order'),
        ])->defaultSort('sort_order')->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
