<?php

namespace App\Filament\Resources\Deals\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DealForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Deal')->schema([
                Select::make('store_id')->relationship('store', 'name')->searchable()->preload()->required()->label('Store'),
                TextInput::make('title')->required(),
                TextInput::make('image')->url()->label('Image URL')->columnSpanFull(),
                Textarea::make('description')->rows(2)->columnSpanFull(),
                TextInput::make('original_price')->numeric()->prefix('$')->label('Original price'),
                TextInput::make('sale_price')->numeric()->prefix('$')->label('Sale price'),
                TextInput::make('currency')->default('USD')->maxLength(3),
                TextInput::make('destination_url')->refererPicker()->url()->label('Where it links to'),
            ])->columns(2),

            Section::make('Validity & status')->schema([
                DateTimePicker::make('starts_at')->label('Starts'),
                DateTimePicker::make('expires_at')->label('Expires'),
                Toggle::make('is_featured')->label('Feature on homepage'),
                Select::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'])->default('published')->required(),
                TextInput::make('sort_order')->numeric()->default(0),
            ])->columns(2),
        ]);
    }
}
