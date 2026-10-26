<?php
namespace App\Filament\Resources\HomeBanners\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomeBannerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('headline'),
            TextInput::make('subhead'),
            TextInput::make('image')->url()->label('Image URL (desktop)')->columnSpanFull(),
            TextInput::make('mobile_image')->url()->label('Image URL (mobile, optional)')->columnSpanFull(),
            TextInput::make('link_url')->url()->label('Links to'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
