<?php
namespace App\Filament\Resources\HomeBlocks\Schemas;

use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Repeater;
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
                'intro' => 'Intro heading with live counts',
                'hero' => 'Hero banner carousel',
                'featured_logos' => 'Featured store logos',
                'offer_feed' => 'Coupons & deals list (with Load more)',
                'coupon_grid' => 'Latest coupons grid',
                'category_boxes' => 'Shop by category (boxes with sub-categories)',
                'stores_by_category' => 'Featured stores by category',
                'trending' => 'Trending / featured categories',
                'handpicked' => 'Handpicked cards',
                'blog_feed' => 'Latest blog posts',
                'why_us' => 'Why choose us (feature points)',
                'faq' => 'FAQ (also tells Google)',
                'rich_text' => 'Custom text block',
            ]),
            TextInput::make('title')->helperText('Heading shown above the block (optional).'),
            Textarea::make('config.text')->label('Text')->rows(4)
                ->visible(fn ($get) => in_array($get('type'), ['rich_text', 'intro']))
                ->helperText(fn ($get) => $get('type') === 'intro' ? 'Optional line under the heading. The live counts line is added automatically.' : null)
                ->columnSpanFull(),
            Repeater::make('config.points')->label('Points')
                ->schema([
                    TextInput::make('icon')->label('Icon (emoji)')->maxLength(8),
                    TextInput::make('title')->required(),
                    Textarea::make('text')->rows(2)->columnSpanFull(),
                ])->columns(2)
                ->helperText('Leave empty to use the built-in starter points.')
                ->visible(fn ($get) => $get('type') === 'why_us')->columnSpanFull(),
            Repeater::make('config.faqs')->label('Questions')
                ->schema([
                    TextInput::make('question')->required()->columnSpanFull(),
                    Textarea::make('answer')->required()->rows(3)->columnSpanFull(),
                ])
                ->helperText('Leave empty to use the built-in starter questions.')
                ->visible(fn ($get) => $get('type') === 'faq')->columnSpanFull(),
            TextInput::make('config.limit')->numeric()->label('How many to show')
                ->helperText(fn ($get) => $get('type') === 'offer_feed' ? 'Offers shown at first, and added by each "Load more" click.' : null)
                ->visible(fn ($get) => in_array($get('type'), ['offer_feed', 'category_boxes', 'stores_by_category', 'coupon_grid', 'blog_feed', 'featured_logos', 'handpicked', 'trending']))
                ->default(8),
            KeyValue::make('config')->label('Advanced settings')
                ->visible(fn ($get) => false),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(2);
    }
}
