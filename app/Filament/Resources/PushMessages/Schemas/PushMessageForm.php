<?php

namespace App\Filament\Resources\PushMessages\Schemas;

use App\Support\PushTargets;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Html;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\HtmlString;

class PushMessageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('1. What are you promoting?')
                    ->description('Pick a store, coupon, deal, product, roundup, blog post or page and the notification below is written for you. You can still change anything.')
                    ->schema([
                        TextInput::make('name')
                            ->label('Campaign name (only you see this)')
                            ->placeholder('Black Friday – Nike')
                            ->maxLength(255),
                        Select::make('promote_type')
                            ->label('Promote')
                            ->options(PushTargets::types())
                            ->default('link')
                            ->required()
                            ->native(false)
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('promote_id', null)),
                        Select::make('promote_id')
                            ->label(fn (Get $get): string => 'Choose the '.strtolower(PushTargets::label($get('promote_type'))))
                            ->visible(fn (Get $get): bool => filled($get('promote_type')) && $get('promote_type') !== 'link')
                            ->required(fn (Get $get): bool => filled($get('promote_type')) && $get('promote_type') !== 'link')
                            ->searchable()
                            ->options(fn (Get $get): array => PushTargets::search($get('promote_type')))
                            ->getSearchResultsUsing(fn (?string $search, Get $get): array => PushTargets::search($get('promote_type'), $search))
                            ->getOptionLabelUsing(fn ($value, Get $get): ?string => PushTargets::optionLabel($get('promote_type'), PushTargets::find($get('promote_type'), $value)))
                            ->helperText('Type to search.')
                            ->live()
                            ->afterStateUpdated(function ($state, Get $get, Set $set): void {
                                foreach (PushTargets::suggest($get('promote_type'), $state) ?? [] as $field => $value) {
                                    $set($field, $value);
                                }
                            })
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make('2. The notification')
                    ->description('The preview on the right updates as you type.')
                    ->schema([
                        Group::make([
                            TextInput::make('title')
                                ->required()
                                ->maxLength(65)
                                ->live(debounce: 600)
                                ->placeholder('40% off Nike today only')
                                ->helperText('About 40 characters show on phones.'),
                            Textarea::make('body')
                                ->label('Message')
                                ->required()
                                ->maxLength(160)
                                ->rows(3)
                                ->live(debounce: 600)
                                ->placeholder('Verified code inside. Ends at midnight.'),
                            TextInput::make('url')
                                ->label('Page to open when clicked')
                                ->url()
                                ->maxLength(1000)
                                ->live(onBlur: true)
                                ->placeholder('https://gipsypockets.store/store/nike')
                                ->helperText('Filled in automatically when you pick something above. Empty = home page.'),
                            TextInput::make('image_url')
                                ->label('Large image (optional)')
                                ->url()
                                ->maxLength(1000)
                                ->live(onBlur: true)
                                ->helperText('Shown on Chrome and Android. Wide images (2:1) look best.'),
                        ]),
                        Html::make(fn (Get $get): HtmlString => new HtmlString(view('filament.push-preview', [
                            'title' => $get('title'),
                            'body' => $get('body'),
                            'url' => $get('url'),
                            'image' => $get('image_url'),
                        ])->render())),
                    ])
                    ->columns(2),

                Section::make('3. When to send')
                    ->schema([
                        DateTimePicker::make('scheduled_at')
                            ->label('Send automatically at (optional)')
                            ->seconds(false)
                            ->native(false)
                            ->helperText('Leave empty, save, then use "Send test to me" and "Send now". Times are in '.config('app.timezone').'. Automatic sending needs the hPanel cron job running.'),
                    ]),
            ])
            ->columns(1);
    }
}
