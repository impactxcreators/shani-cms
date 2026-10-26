<?php
namespace App\Filament\Resources\HomeBlocks\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class HomeBlockForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')->required()->live()->options([
                'hero' => 'Hero banner carousel',
                'featured_logos' => 'Featured store logos',
                'coupon_grid' => 'Latest coupons grid',
                'trending' => 'Trending / featured categories',
                'handpicked' => 'Handpicked cards',
                'blog_feed' => 'Latest blog posts',
                'rich_text' => 'Custom text block',
            ]),
            TextInput::make('title')->helperText('Heading shown above the block (optional).'),
            Textarea::make('config.text')->label('Text')->rows(4)
                ->visible(fn ($get) => $get('type') === 'rich_text')->columnSpanFull(),
            TextInput::make('config.limit')->numeric()->label('How many to show')
                ->visible(fn ($get) => in_array($get('type'), ['coupon_grid', 'blog_feed', 'featured_logos', 'handpicked', 'trending']))
                ->default(8),
            KeyValue::make('config')->label('Advanced settings')
                ->visible(fn ($get) => false),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
