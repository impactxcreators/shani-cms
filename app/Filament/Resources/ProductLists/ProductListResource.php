<?php

namespace App\Filament\Resources\ProductLists;

use App\Filament\Resources\ProductLists\Pages\CreateProductList;
use App\Filament\Resources\ProductLists\Pages\EditProductList;
use App\Filament\Resources\ProductLists\Pages\ListProductLists;
use App\Filament\Resources\ProductLists\Schemas\ProductListForm;
use App\Filament\Resources\ProductLists\Tables\ProductListsTable;
use App\Models\ProductList;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use UnitEnum;

class ProductListResource extends Resource
{
    protected static ?string $model = ProductList::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;
    protected static string|UnitEnum|null $navigationGroup = 'Products';
    protected static ?string $navigationLabel = 'Roundup Lists';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema { return ProductListForm::configure($schema); }
    public static function table(Table $table): Table { return ProductListsTable::configure($table); }
    public static function getPages(): array {
        return [
            'index' => ListProductLists::route('/'),
            'create' => CreateProductList::route('/create'),
            'edit' => EditProductList::route('/{record}/edit'),
        ];
    }
    public static function getRecordRouteBindingEloquentQuery(): Builder {
        return parent::getRecordRouteBindingEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }
}
