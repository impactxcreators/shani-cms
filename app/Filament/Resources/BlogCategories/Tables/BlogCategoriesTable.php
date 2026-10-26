<?php

namespace App\Filament\Resources\BlogCategories\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BlogCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('slug')->color('gray'),
            TextColumn::make('posts_count')->counts('posts')->label('Posts'),
        ])->recordActions([EditAction::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
