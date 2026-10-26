<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageSiteSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $title = 'Site Settings';

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.manage-site-settings';

    public ?array $data = [];

    /** key => type */
    protected array $fields = [
        'site_name' => 'string',
        'tagline' => 'string',
        'site_description' => 'text',
        'owner_email' => 'string',
        'affiliate_disclosure' => 'text',
        'maintenance_mode' => 'bool',
        'maintenance_message' => 'text',
    ];

    public function mount(): void
    {
        $values = [];
        foreach ($this->fields as $key => $type) {
            $values[$key] = Setting::get($key);
        }
        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Site identity')
                    ->schema([
                        TextInput::make('site_name')->required(),
                        TextInput::make('tagline'),
                        Textarea::make('site_description')->rows(2),
                        TextInput::make('owner_email')->email(),
                    ])
                    ->columns(2),

                Section::make('Coming soon / maintenance')
                    ->description('When turned on, visitors see a "coming soon" page. You (logged in) always see the real site.')
                    ->schema([
                        Toggle::make('maintenance_mode')->label('Show "coming soon" to visitors'),
                        Textarea::make('maintenance_message')->rows(2),
                    ]),

                Section::make('Legal')
                    ->schema([
                        Textarea::make('affiliate_disclosure')->rows(2)
                            ->helperText('Shown in the site footer. Required by most affiliate programmes.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach ($this->fields as $key => $type) {
            Setting::set($key, $data[$key] ?? ($type === 'bool' ? '0' : ''), $type);
        }

        Notification::make()->title('Settings saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Save settings')->submit('save'),
        ];
    }
}
