<?php

namespace App\Filament\Resources\Stores;

use App\Filament\Resources\Stores\Pages\CreateStore;
use App\Filament\Resources\Stores\Pages\EditStore;
use App\Filament\Resources\Stores\Pages\ListStores;
use App\Filament\Resources\Stores\Schemas\StoreForm;
use App\Filament\Resources\Stores\Tables\StoresTable;
use App\Models\Store;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class StoreResource extends Resource
{
    protected static ?string $model = Store::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;
    protected static string|UnitEnum|null $navigationGroup = 'Stores';
    protected static ?string $navigationLabel = 'All Stores';
    protected static ?string $recordTitleAttribute = 'name';
    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema { return StoreForm::configure($schema); }
    public static function table(Table $table): Table { return StoresTable::configure($table); }
    public static function getPages(): array {
        return [
            'index' => ListStores::route('/'),
            'create' => CreateStore::route('/create'),
            'edit' => EditStore::route('/{record}/edit'),
        ];
    }
    public static function getRecordRouteBindingEloquentQuery(): Builder {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
