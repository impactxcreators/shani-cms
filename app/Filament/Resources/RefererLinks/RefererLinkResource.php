<?php

namespace App\Filament\Resources\RefererLinks;

use App\Filament\Resources\RefererLinks\Pages\BulkResults;
use App\Filament\Resources\RefererLinks\Pages\CreateRefererLink;
use App\Filament\Resources\RefererLinks\Pages\EditRefererLink;
use App\Filament\Resources\RefererLinks\Pages\ListRefererLinks;
use App\Filament\Resources\RefererLinks\Schemas\RefererLinkForm;
use App\Filament\Resources\RefererLinks\Tables\RefererLinksTable;
use App\Models\RefererLink;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class RefererLinkResource extends Resource
{
    protected static ?string $model = RefererLink::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedLink;

    protected static string|UnitEnum|null $navigationGroup = 'Tools';

    protected static ?string $navigationLabel = 'Referers';

    protected static ?string $modelLabel = 'referer link';

    protected static ?string $recordTitleAttribute = 'code';

    public static function getNavigationBadge(): ?string
    {
        $broken = RefererLink::whereHas('destinations', fn ($q) => $q->where('check_status', 'broken'))->count();

        return $broken ? (string) $broken : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Links with a broken destination';
    }

    public static function form(Schema $schema): Schema
    {
        return RefererLinkForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RefererLinksTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRefererLinks::route('/'),
            'create' => CreateRefererLink::route('/create'),
            'batch' => BulkResults::route('/batch/{batch}'),
            'edit' => EditRefererLink::route('/{record}/edit'),
        ];
    }
}
