<?php

namespace App\Filament\Resources\Deals\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class DealsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable()->weight('bold')->wrap(),
            TextColumn::make('store.name')->searchable()->sortable()->label('Store'),
            TextColumn::make('sale_price')->money('USD')->label('Sale'),
            TextColumn::make('original_price')->money('USD')->label('Was')->color('gray'),
            IconColumn::make('is_featured')->boolean()->label('Featured'),
            TextColumn::make('expires_at')->date()->label('Expires')->placeholder('—'),
            TextColumn::make('status')->badge()->colors(['gray'=>'draft','success'=>'published','warning'=>'archived']),
        ])->defaultSort(Duplicate::sort('sort_order'))
          ->filters([\App\Support\Describer\DescribeAction::copiesFilter(), 
              SelectFilter::make('store')->relationship('store', 'name')->searchable()->preload(),
              SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived']),
              TrashedFilter::make(),
          ])
          ->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([
                \App\Support\Describer\DescribeAction::bulk(),
              DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make(),
          ])]);
    }
}
