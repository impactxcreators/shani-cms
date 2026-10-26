<?php

namespace App\Filament\Resources\ProductLists\Schemas;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductList;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class ProductListForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Page heading')->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')->unique(ignoreRecord: true),
                TextInput::make('h1')->label('Big heading (H1)')->helperText('Shown on the page. Defaults to the title.'),
                TextInput::make('subtitle'),
                TextInput::make('updated_label')->label('Updated label')->placeholder('Oct 2026'),
                Select::make('product_category_id')->label('Category')
                    ->options(fn () => ProductCategory::pluck('name', 'id'))->searchable()->nullable(),
                TextInput::make('banner')->url()->label('Banner image URL')->columnSpanFull(),
                RichEditor::make('intro')->columnSpanFull(),
            ])->columns(2),

            Section::make('Ranked products')
                ->description('Drag the handle to reorder. Rank is set by the order here.')
                ->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->orderColumn('rank')
                        ->reorderable()
                        ->collapsible()
                        ->itemLabel(fn (array $state) => $state['custom_title'] ?? null)
                        ->schema([
                            Select::make('product_id')->label('Product')
                                ->options(fn () => Product::orderBy('title')->pluck('title', 'id'))
                                ->searchable()->required(),
                            TextInput::make('badge')->placeholder('Best Overall, Best Budget…'),
                            TextInput::make('custom_title')->label('Override title'),
                            Textarea::make('custom_blurb')->rows(2),
                            Textarea::make('editor_note')->rows(2)->label('Editor note'),
                        ])->columns(2),
                ]),

            Section::make('FAQ')->collapsed()->schema([
                Repeater::make('faq_items')->label('Questions')
                    ->schema([
                        TextInput::make('question')->required(),
                        Textarea::make('answer')->rows(2)->required(),
                    ])->columns(1)->addActionLabel('Add question'),
            ]),

            Section::make('Publishing & SEO')->schema([
                Select::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'])->default('draft')->required(),
                DateTimePicker::make('published_at')->label('Publish date'),
                Select::make('related_list_ids')->label('Related roundups')->multiple()
                    ->options(fn () => ProductList::pluck('title', 'id'))->searchable(),
                RichEditor::make('outro')->columnSpanFull(),
                TextInput::make('meta_title')->maxLength(60),
                Textarea::make('meta_description')->maxLength(160)->rows(2),
                Toggle::make('no_index')->label('Hide from search engines'),
            ])->columns(2),
        ]);
    }
}
