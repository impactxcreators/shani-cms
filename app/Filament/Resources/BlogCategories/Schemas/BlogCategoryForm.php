<?php

namespace App\Filament\Resources\BlogCategories\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class BlogCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->unique(ignoreRecord: true),
            Textarea::make('description')->rows(2)->columnSpanFull(),
        ])->columns(2);
    }
}
