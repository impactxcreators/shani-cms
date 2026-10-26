<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Author;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Post')->schema([
                TextInput::make('title')->required()->live(onBlur: true)
                    ->afterStateUpdated(fn ($state, $get, $set) => blank($get('slug')) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')->unique(ignoreRecord: true),
                Textarea::make('excerpt')->rows(2)->maxLength(500)->columnSpanFull(),
                TextInput::make('cover_image')->url()->label('Cover image URL')->columnSpanFull(),
                RichEditor::make('body')->columnSpanFull(),
            ])->columns(2),

            Section::make('Organise')->schema([
                Select::make('author_id')->label('Author')->options(fn () => Author::pluck('name', 'id'))
                    ->searchable()->createOptionForm([TextInput::make('name')->required()]),
                Select::make('categories')->relationship('categories', 'name')->multiple()->preload()->searchable()
                    ->createOptionForm([TextInput::make('name')->required()]),
                TagsInput::make('tags')->columnSpanFull(),
            ])->columns(2),

            Section::make('Publishing & SEO')->schema([
                Select::make('status')->options(['draft'=>'Draft','published'=>'Published','archived'=>'Archived'])->default('draft')->required(),
                DateTimePicker::make('published_at')->label('Publish date'),
                TextInput::make('reading_minutes')->numeric()->label('Reading time (min)'),
                TextInput::make('meta_title')->maxLength(60),
                Textarea::make('meta_description')->maxLength(160)->rows(2),
                Toggle::make('no_index')->label('Hide from search engines'),
            ])->columns(2),
        ]);
    }
}
