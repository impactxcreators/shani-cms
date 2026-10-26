<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Models\ProductCategory;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Product')->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('short_title')->helperText('Optional shorter name for lists.'),
                TextInput::make('slug')->unique(ignoreRecord: true),
                TextInput::make('brand'),
                Select::make('product_category_id')->label('Category')
                    ->options(fn () => ProductCategory::pluck('name', 'id'))->searchable()->nullable(),
                TextInput::make('image_url')->url()->label('Image URL')->columnSpanFull(),
                TagsInput::make('highlights')->helperText('Key selling points. Press Enter after each.')->columnSpanFull(),
                RichEditor::make('description')->columnSpanFull(),
            ])->columns(2),

            Section::make('Pricing & rating')->schema([
                TextInput::make('price')->numeric()->prefix('$'),
                TextInput::make('currency')->default('USD')->maxLength(3),
                TextInput::make('rating')->numeric()->minValue(0)->maxValue(5)->step(0.1),
                TextInput::make('review_count')->numeric(),
                TextInput::make('affiliate_url')->url()->label('Affiliate link')->columnSpanFull(),
                TextInput::make('external_id')->label('ASIN / retailer id'),
                Toggle::make('is_available')->default(true)->label('In stock'),
            ])->columns(2),
        ]);
    }
}
