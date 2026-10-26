<?php
namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable()->weight('bold'),
            TextColumn::make('email')->searchable()->color('gray'),
            TextColumn::make('roles.name')->badge()->label('Roles'),
            IconColumn::make('is_active')->boolean()->label('Active'),
            TextColumn::make('last_login_at')->since()->label('Last login')->placeholder('Never'),
        ])->recordActions([EditAction::make()])
          ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }
}
