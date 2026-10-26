<?php
namespace App\Filament\Resources\Adverts;

use App\Filament\Resources\Adverts\Pages\CreateAdvert;
use App\Filament\Resources\Adverts\Pages\EditAdvert;
use App\Filament\Resources\Adverts\Pages\ListAdverts;
use App\Filament\Resources\Adverts\Schemas\AdvertForm;
use App\Filament\Resources\Adverts\Tables\AdvertsTable;
use App\Models\Advert;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class AdvertResource extends Resource
{
    protected static ?string $model = Advert::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;
    protected static string|UnitEnum|null $navigationGroup = 'Content';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema { return AdvertForm::configure($schema); }
    public static function table(Table $table): Table { return AdvertsTable::configure($table); }
    public static function getPages(): array {
        return [
            'index' => ListAdverts::route('/'),
            'create' => CreateAdvert::route('/create'),
            'edit' => EditAdvert::route('/{record}/edit'),
        ];
    }
}
