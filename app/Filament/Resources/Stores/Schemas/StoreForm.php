<?php

namespace App\Filament\Resources\Stores\Schemas;

use App\Models\Category;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class StoreForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Store details')->schema([
                TextInput::make('name')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')->unique(ignoreRecord: true)->helperText('Leave blank to auto-fill.'),
                TextInput::make('website_url')->url()->label('Website URL'),
                TextInput::make('affiliate_url')->refererPicker()->url()->label('Affiliate link')
                    ->helperText('Your tracked link to this store.'),
                FileUpload::make('logo')->image()->disk('public')->directory('store-logos')->imageEditor()->maxSize(2048)->label('Logo')->helperText('Upload a logo image (PNG or JPG, up to 2 MB).')->columnSpanFull(),
                RichEditor::make('description')->hintAction(\App\Support\Describer\DescribeAction::make())->columnSpanFull(),
                Select::make('categories')->relationship('categories', 'name')
                    ->multiple()->preload()->searchable()
                    ->createOptionForm([
                        TextInput::make('name')->required(),
                    ])->columnSpanFull(),
            ])->columns(2),

            Section::make('Options')->schema([
                Toggle::make('is_active')->default(true)->label('Active'),
                Toggle::make('is_featured')->label('Feature on homepage'),
                TextInput::make('average_rating')->numeric()->minValue(0)->maxValue(5)->step(0.1)->label('Rating (0–5)'),
                Select::make('status')->options([
                    'draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived',
                ])->default('published')->required(),
            ])->columns(2),

            Section::make('SEO')->collapsed()->schema([
                TextInput::make('meta_title')->maxLength(60),
                Textarea::make('meta_description')->maxLength(160)->rows(2),
                Toggle::make('no_index')->label('Hide from search engines'),
            ])->columns(2),
        ]);
    }
}
