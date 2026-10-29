<?php

namespace App\Filament\Resources\RefererLinks\Pages;

use App\Filament\Resources\RefererLinks\RefererLinkResource;
use App\Models\RefererLink;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Icons\Heroicon;

class ListRefererLinks extends ListRecords
{
    protected static string $resource = RefererLinkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('bulk')
                ->label('Bulk add')
                ->icon(Heroicon::OutlinedQueueList)
                ->color('gray')
                ->modalHeading('Bulk referer links')
                ->modalSubmitActionLabel('Create short links')
                ->schema([
                    Select::make('type')
                        ->label('Referer type')
                        ->options(RefererLink::TYPES)
                        ->default('direct')
                        ->required()
                        ->native(false)
                        ->live()
                        ->helperText(fn (Get $get): string => $get('type') === 'rotational'
                            ? 'All the links below become ONE short link that rotates between them.'
                            : 'Each line below gets its own short link.'),
                    Radio::make('rotation_mode')
                        ->label('How to rotate')
                        ->options(RefererLink::MODES)
                        ->default('in_order')
                        ->visible(fn (Get $get): bool => $get('type') === 'rotational'),
                    Textarea::make('links')
                        ->label('Bulk links')
                        ->required()
                        ->rows(12)
                        ->placeholder("https://www.awin1.com/cread.php?awinmid=95167&awinaffid=1946277\nhttps://drinkbev.pxf.io/b3RYrx\n…")
                        ->helperText('One link per line, up to 1,000. Lines that are not web addresses are skipped and listed afterwards.')
                        ->rule(fn () => function (string $attribute, $value, \Closure $fail) {
                            if (count(array_filter(preg_split('/\r\n|\r|\n/', (string) $value), 'trim')) > 1000) {
                                $fail('Please paste at most 1,000 links at a time.');
                            }
                        }),
                    TextInput::make('name')
                        ->label('Name / note (optional, added to every link)')
                        ->maxLength(255),
                ])
                ->action(function (array $data) {
                    $r = RefererLink::bulkCreate($data['type'], $data['rotation_mode'] ?? null, $data['links'], $data['name'] ?? null);
                    session()->put('referer_batch_invalid.'.$r['batch'], $r['invalid']);

                    if ($r['created'] === 0) {
                        Notification::make()->title('No valid links found')->body('Each line must be a web address.')->danger()->send();

                        return;
                    }

                    return redirect(RefererLinkResource::getUrl('batch', ['batch' => $r['batch']]));
                }),
            CreateAction::make()->label('Add Referer Link'),
        ];
    }
}
