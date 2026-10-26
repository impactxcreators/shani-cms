<?php
namespace App\Filament\Resources\HomeBlocks;

use App\Filament\Resources\HomeBlocks\Pages\CreateHomeBlock;
use App\Filament\Resources\HomeBlocks\Pages\EditHomeBlock;
use App\Filament\Resources\HomeBlocks\Pages\ListHomeBlocks;
use App\Filament\Resources\HomeBlocks\Schemas\HomeBlockForm;
use App\Filament\Resources\HomeBlocks\Tables\HomeBlocksTable;
use App\Models\HomeBlock;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HomeBlockResource extends Resource
{
    protected static ?string $model = HomeBlock::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleGroup;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?string $navigationLabel = 'Home Blocks';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema { return HomeBlockForm::configure($schema); }
    public static function table(Table $table): Table { return HomeBlocksTable::configure($table); }
    public static function getPages(): array {
        return [
            'index' => ListHomeBlocks::route('/'),
            'create' => CreateHomeBlock::route('/create'),
            'edit' => EditHomeBlock::route('/{record}/edit'),
        ];
    }
}
