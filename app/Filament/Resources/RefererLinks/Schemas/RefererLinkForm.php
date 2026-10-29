<?php

namespace App\Filament\Resources\RefererLinks\Schemas;

use App\Models\RefererDestination;
use App\Models\RefererLink;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class RefererLinkForm
{
    public static function configure(Schema $schema): Schema
    {
        $rotational = fn (Get $get): bool => $get('type') === 'rotational';
        $inItemRotational = fn (Get $get): bool => $get('../../type') === 'rotational';

        return $schema
            ->components([
                Section::make('Referer type')
                    ->schema([
                        Select::make('type')
                            ->label('Referer type')
                            ->options(RefererLink::TYPES)
                            ->default('direct')
                            ->required()
                            ->native(false)
                            ->live()
                            ->helperText(fn (Get $get): string => match ($get('type')) {
                                'blank' => 'One destination. Hides your site: the merchant cannot see where the visitor came from.',
                                'rotational' => 'Several destinations. Each click goes to a different one.',
                                default => 'One destination. The merchant can see the visitor came from your site (normal for affiliate links).',
                            }),
                        Radio::make('rotation_mode')
                            ->label('How to rotate')
                            ->options(RefererLink::MODES)
                            ->default('in_order')
                            ->visible($rotational)
                            ->required($rotational)
                            ->live(),
                    ]),

                Section::make(fn (Get $get): string => $get('type') === 'rotational' ? 'Destinations' : 'Destination')
                    ->schema([
                        Repeater::make('destinations')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->hiddenLabel()
                            ->defaultItems(1)
                            ->minItems(1)
                            ->maxItems(fn (Get $get): ?int => $get('type') === 'rotational' ? null : 1)
                            ->addable($rotational)
                            ->deletable($rotational)
                            ->reorderable($rotational)
                            ->addActionLabel('Add another destination')
                            ->schema([
                                TextInput::make('url')
                                    ->label('Destination URL')
                                    ->required()
                                    ->maxLength(2000)
                                    ->placeholder('https://www.awin1.com/cread.php?awinmid=…')
                                    ->live(onBlur: true)
                                    ->dehydrateStateUsing(fn ($state) => RefererLink::normalizeUrl($state))
                                    ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                                        if (! RefererLink::isValidUrl($value)) {
                                            $fail('Enter a valid web address.');
                                        }
                                    })
                                    ->helperText(function ($state, ?RefererDestination $record): ?string {
                                        $notes = [];
                                        if ($record?->check_status === 'broken') {
                                            $notes[] = '⚠ Last check: not loading'.($record->check_code ? ' (error '.$record->check_code.')' : '').', '.$record->checked_at?->diffForHumans().'.';
                                        }
                                        if ($dup = RefererLink::duplicateNotice($state, $record?->referer_link_id)) {
                                            $notes[] = $dup;
                                        }

                                        return $notes ? implode(' ', $notes) : null;
                                    })
                                    ->columnSpan(['default' => 4, 'md' => 2]),
                                TextInput::make('weight')
                                    ->label('Share')
                                    ->numeric()
                                    ->minValue(0)
                                    ->default(1)
                                    ->helperText('e.g. 70 and 30')
                                    ->visible(fn (Get $get): bool => $get('../../type') === 'rotational' && $get('../../rotation_mode') === 'weighted'),
                                Toggle::make('is_active')
                                    ->label('In rotation')
                                    ->default(true)
                                    ->inline(false)
                                    ->visible($inItemRotational),
                                TextInput::make('click_count')
                                    ->label('Clicks')
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visible(fn (?RefererDestination $record): bool => $record !== null),
                            ])
                            ->columns(['default' => 4, 'md' => 5]),
                    ]),

                Section::make('Short link')
                    ->schema([
                        TextInput::make('code')
                            ->label('Short code')
                            ->prefix(url('/ref').'/')
                            ->placeholder('Leave empty for a random code')
                            ->maxLength(100)
                            ->regex('/^[A-Za-z0-9][A-Za-z0-9_\-\/.]*$/')
                            ->unique(ignoreRecord: true)
                            ->validationMessages(['regex' => 'Use letters, numbers, - _ . / only.'])
                            ->helperText('Optional. e.g. nike-black-friday. Codes are case-sensitive.'),
                        TextInput::make('name')
                            ->label('Name / note (only you see this)')
                            ->placeholder('Awin – Nike UK')
                            ->maxLength(255),
                        Toggle::make('is_active')->label('Active')->default(true)->inline(false),
                        DateTimePicker::make('expires_at')
                            ->label('Stop working after (optional)')
                            ->native(false)
                            ->seconds(false),
                        TextInput::make('fallback_url')
                            ->label('When off or expired, send visitors to')
                            ->url()
                            ->maxLength(2000)
                            ->placeholder(url('/'))
                            ->helperText('Empty = your home page.')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ])
            ->columns(1);
    }
}
