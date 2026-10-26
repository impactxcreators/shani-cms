<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Page content')
                    ->schema([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $get, $set) {
                                if (blank($get('slug'))) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('slug')
                            ->helperText('The web address part, e.g. "about-us". Leave blank to auto-fill from the title.')
                            ->unique(ignoreRecord: true),
                        RichEditor::make('body')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('Publishing')
                    ->schema([
                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                                'archived' => 'Archived',
                            ])
                            ->default('draft')
                            ->required(),
                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->helperText('Leave blank to publish immediately when status is Published.'),
                        Toggle::make('show_in_header')->label('Show link in header menu'),
                        Toggle::make('show_in_footer')->label('Show link in footer'),
                        TextInput::make('sort_order')->numeric()->default(0)->label('Menu order'),
                    ])
                    ->columns(2),

                Section::make('SEO (search engines)')
                    ->collapsed()
                    ->schema([
                        TextInput::make('meta_title')
                            ->maxLength(60)
                            ->helperText('Title shown in Google results. Aim for under 60 characters.'),
                        Textarea::make('meta_description')
                            ->maxLength(160)
                            ->rows(2)
                            ->helperText('Summary shown in Google results. Aim for under 160 characters.'),
                        TextInput::make('canonical_url')->url(),
                        Toggle::make('no_index')->label('Hide this page from search engines'),
                    ])
                    ->columns(2),
            ]);
    }
}
