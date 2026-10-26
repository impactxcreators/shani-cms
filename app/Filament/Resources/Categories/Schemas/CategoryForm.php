<?php

namespace App\Filament\Resources\Categories\Schemas;

use App\Models\Category;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->unique(ignoreRecord: true)
                ->helperText('Leave blank to auto-fill from the name.'),
            Select::make('parent_id')->label('Parent category')
                ->options(fn () => Category::pluck('name', 'id'))->searchable()->nullable(),
            Textarea::make('description')->rows(2)->columnSpanFull(),
            TextInput::make('icon')->helperText('Optional icon name or emoji.'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_featured')->label('Feature on homepage'),
        ])->columns(2);
    }
}
