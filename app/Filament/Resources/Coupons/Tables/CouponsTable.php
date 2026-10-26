<?php

namespace App\Filament\Resources\Coupons\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CouponsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable()->weight('bold')->wrap(),
            TextColumn::make('store.name')->searchable()->sortable()->label('Store'),
            TextColumn::make('code')->badge()->color('gray')->placeholder('—'),
            TextColumn::make('discount_type')->badge()->label('Type'),
            IconColumn::make('is_verified')->boolean()->label('Verified'),
            TextColumn::make('expires_at')->date()->label('Expires')->placeholder('—')
                ->color(fn ($record) => $record->expires_at && $record->expires_at->isPast() ? 'danger' : null),
            TextColumn::make('used_count')->label('Used')->numeric(),
            TextColumn::make('status')->badge()->colors(['gray'=>'draft','success'=>'published','warning'=>'archived']),
        ])->defaultSort('sort_order')
          ->filters([
              SelectFilter::make('store')->relationship('store', 'name')->searchable()->preload(),
              SelectFilter::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived']),
              TrashedFilter::make(),
          ])
          ->recordActions([EditAction::make()])
          ->toolbarActions([BulkActionGroup::make([
              DeleteBulkAction::make(), ForceDeleteBulkAction::make(), RestoreBulkAction::make(),
          ])]);
    }
}
