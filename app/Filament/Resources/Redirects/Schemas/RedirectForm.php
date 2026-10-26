<?php

namespace App\Filament\Resources\Redirects\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class RedirectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('source')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->placeholder('/old-page')
                    ->helperText('The old address on your site, starting with a slash, e.g. /old-page'),
                TextInput::make('destination')
                    ->required()
                    ->placeholder('/new-page or https://example.com')
                    ->helperText('Where visitors should be sent instead.'),
                Select::make('status_code')
                    ->label('Redirect type')
                    ->options([
                        301 => '301 — Permanent move (recommended)',
                        302 => '302 — Temporary move',
                        410 => '410 — Gone (page removed)',
                    ])
                    ->default(301)
                    ->required(),
                Toggle::make('is_active')->default(true)->label('Active'),
            ])
            ->columns(1);
    }
}
