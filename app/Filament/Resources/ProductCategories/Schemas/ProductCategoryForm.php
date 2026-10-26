<?php

namespace App\Filament\Resources\ProductCategories\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->live(onBlur: true)
                ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
            TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Leave blank to auto-fill.'),
            Select::make('parent_id')->label('Parent')->options(fn () => ProductCategory::pluck('name', 'id'))->searchable()->nullable(),
            Textarea::make('description')->rows(2)->columnSpanFull(),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_featured'),
        ])->columns(2);
    }
}
