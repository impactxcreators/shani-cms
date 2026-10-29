<?php
namespace App\Filament\Resources\HomeBanners\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class HomeBannersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('image')->label('')->height(40),
            TextColumn::make('headline')->searchable()->weight('bold'),
            IconColumn::make('is_active')->boolean()->label('Active'),
            TextColumn::make('sort_order')->label('Order')->sortable(),
        ])->defaultSort(Duplicate::sort('sort_order'))->reorderable('sort_order')
          ->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
