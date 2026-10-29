<?php
namespace App\Filament\Resources\Adverts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AdvertForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('placement')->options([
                'home_top' => 'Homepage — top',
                'home_mid' => 'Homepage — middle',
                'sidebar' => 'Sidebar',
                'in_content' => 'In content',
            ])->required(),
            TextInput::make('image')->url()->label('Image URL'),
            TextInput::make('destination_url')->refererPicker()->url()->label('Links to'),
            Textarea::make('html')->rows(3)->label('Or custom HTML')->columnSpanFull(),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('ends_at'),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
