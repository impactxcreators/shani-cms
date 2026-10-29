<?php

namespace App\Filament\Resources\Products\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image_url')->label('')->height(40)->square(),
            TextColumn::make('title')->searchable()->sortable()->weight('bold')->wrap(),
            TextColumn::make('brand')->color('gray')->searchable(),
            TextColumn::make('category.name')->label('Category')->placeholder('—'),
            TextColumn::make('price')->money('USD'),
            TextColumn::make('rating')->label('★')->placeholder('—'),
            IconColumn::make('is_available')->boolean()->label('Stock'),
        ])->defaultSort(Duplicate::sort('updated_at', 'desc'))
          ->filters([\App\Support\Describer\DescribeAction::copiesFilter(), TrashedFilter::make()])
          ->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([
                \App\Support\Describer\DescribeAction::bulk(),
              DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make(),
          ])]);
    }
}
