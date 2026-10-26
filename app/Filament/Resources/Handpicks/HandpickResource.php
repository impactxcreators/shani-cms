<?php

namespace App\Filament\Resources\Handpicks;

use App\Filament\Resources\Handpicks\Pages\CreateHandpick;
use App\Filament\Resources\Handpicks\Pages\EditHandpick;
use App\Filament\Resources\Handpicks\Pages\ListHandpicks;
use App\Filament\Resources\Handpicks\Schemas\HandpickForm;
use App\Filament\Resources\Handpicks\Tables\HandpicksTable;
use App\Models\Handpick;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class HandpickResource extends Resource
{
    protected static ?string $model = Handpick::class;
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHandThumbUp;
    protected static string|UnitEnum|null $navigationGroup = 'Editorial';
    protected static ?string $recordTitleAttribute = 'title';
    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema { return HandpickForm::configure($schema); }
    public static function table(Table $table): Table { return HandpicksTable::configure($table); }
    public static function getPages(): array {
        return [
            'index' => ListHandpicks::route('/'),
            'create' => CreateHandpick::route('/create'),
            'edit' => EditHandpick::route('/{record}/edit'),
        ];
    }
}
