<?php
namespace App\Filament\Resources\Adverts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Support\Duplicate;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdvertsTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('placement')->badge(),
            IconColumn::make('is_active')->boolean()->label('Active'),
            TextColumn::make('impressions')->numeric()->sortable(),
            TextColumn::make('clicks')->numeric()->sortable(),
        ])->defaultSort(Duplicate::sort())->recordActions([EditAction::make(), Duplicate::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
