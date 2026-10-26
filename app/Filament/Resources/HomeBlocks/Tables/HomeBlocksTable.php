<?php
namespace App\Filament\Resources\HomeBlocks\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeBlocksTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('type')->badge(),
            TextColumn::make('title')->placeholder('—')->searchable(),
            IconColumn::make('is_active')->boolean()->label('Active'),
            TextColumn::make('sort_order')->label('Order')->sortable(),
        ])->defaultSort('sort_order')->reorderable('sort_order')
          ->recordActions([EditAction::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
