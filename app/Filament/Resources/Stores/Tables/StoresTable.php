<?php

namespace App\Filament\Resources\Stores\Tables;

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

class StoresTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('coupons_count')->counts('coupons')->label('Coupons'),
            TextColumn::make('deals_count')->counts('deals')->label('Deals'),
            IconColumn::make('is_active')->boolean()->label('Active'),
            IconColumn::make('is_featured')->boolean()->label('Featured'),
            TextColumn::make('status')->badge()->colors([
                'gray' => 'draft', 'success' => 'published', 'warning' => 'archived',
            ]),
            TextColumn::make('updated_at')->since()->label('Updated'),
        ])->defaultSort(Duplicate::sort('updated_at', 'desc'))
          ->filters([\App\Support\Describer\DescribeAction::copiesFilter(), 
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
