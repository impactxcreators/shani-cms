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
        'cookie_banner_enabled' => 'bool',
        'cookie_banner_text' => 'text',
        'consent_scripts' => 'text',
        'push_enabled' => 'bool',
        'push_prompt_title' => 'string',
        'push_prompt_text' => 'text',
        'push_prompt_delay' => 'string',
        'push_prompt_min_pages' => 'string',
    ];

    /** used when a setting has never been saved */
    protected array $defaults = [
        'cookie_banner_enabled' => true,
        'cookie_banner_text' => 'We use essential cookies to run this site. With your OK we also use cookies to understand visits and improve our deals.',
        'push_enabled' => true,
        'push_prompt_title' => 'Get deal alerts?',
        'push_prompt_text' => 'Only the best coupons and price drops, a few times a week at most. Turn off anytime.',
        'push_prompt_delay' => '20',
        'push_prompt_min_pages' => '2',
    ];

    public function mount(): void
    {
        $values = [];
        foreach ($this->fields as $key => $type) {
            $values[$key] = Setting::get($key, $this->defaults[$key] ?? null);
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

                Section::make('Cookie banner')
                    ->description('A small bar at the bottom of the page. It never blocks the page and disappears for good once the visitor picks an option.')
                    ->schema([
                        Toggle::make('cookie_banner_enabled')->label('Show the cookie banner'),
                        Textarea::make('cookie_banner_text')->label('Banner text')->rows(2)
                            ->helperText('A "Privacy policy" link is added automatically.'),
                        Textarea::make('consent_scripts')->label('Tracking code (runs only after "Accept")')->rows(4)
                            ->helperText('Paste Google Analytics, Facebook Pixel, etc. here instead of anywhere else, so it only runs for visitors who agreed.'),
                    ]),

                Section::make('Deal alerts (push notifications)')
                    ->description('Visitors first see a small card asking if they want alerts. The browser\'s own "Allow notifications" popup only appears if they press "Yes". "Not now" hides the card for 14 days.')
                    ->schema([
                        Toggle::make('push_enabled')->label('Offer deal alerts to visitors'),
                        TextInput::make('push_prompt_title')->label('Card title')->maxLength(60),
                        Textarea::make('push_prompt_text')->label('Card text')->rows(2)->maxLength(200),
                        TextInput::make('push_prompt_delay')->label('Wait before asking (seconds)')->numeric()->minValue(0)->maxValue(600),
                        TextInput::make('push_prompt_min_pages')->label('Ask only after this many pages viewed')->numeric()->minValue(1)->maxValue(20),
                    ])
                    ->columns(2),

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
