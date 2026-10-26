<?php

namespace App\Filament\Resources\Authors\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class AuthorForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->unique(ignoreRecord: true),
            TextInput::make('avatar')->url()->label('Avatar URL'),
            Textarea::make('bio')->rows(3)->columnSpanFull(),
        ])->columns(2);
    }
}
