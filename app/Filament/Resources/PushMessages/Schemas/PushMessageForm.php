<?php

namespace App\Filament\Resources\PushMessages\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PushMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(65)
                    ->placeholder('40% off Nike today only')
                    ->helperText('Keep it short: phones cut off long titles (about 40 characters show).'),
                Textarea::make('body')
                    ->label('Message')
                    ->required()
                    ->maxLength(160)
                    ->rows(2)
                    ->placeholder('Verified code inside. Ends at midnight.'),
                TextInput::make('url')
                    ->label('Page to open when clicked')
                    ->url()
                    ->maxLength(1000)
                    ->placeholder('https://gipsypockets.store/store/nike')
                    ->helperText('Leave empty to open the home page.'),
                TextInput::make('image_url')
                    ->label('Large image (optional)')
                    ->url()
                    ->maxLength(1000)
                    ->helperText('Shown on Chrome/Android. Wide images (2:1) look best.'),
            ])
            ->columns(1);
    }
}
