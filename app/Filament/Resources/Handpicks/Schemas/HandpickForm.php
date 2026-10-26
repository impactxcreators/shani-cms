<?php

namespace App\Filament\Resources\Handpicks\Schemas;

use App\Models\Category;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class HandpickForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')->required(),
            TextInput::make('blurb'),
            TextInput::make('image')->url()->label('Image URL'),
            TextInput::make('destination_url')->url()->label('Link'),
            Select::make('category_id')->label('Store category')
                ->options(fn () => Category::pluck('name', 'id'))->searchable()->nullable(),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
