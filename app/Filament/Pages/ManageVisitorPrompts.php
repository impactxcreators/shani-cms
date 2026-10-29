<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use App\Support\VisitorPrompts;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageVisitorPrompts extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Banners & Pop-ups';

    protected static ?string $title = 'Banners & Pop-ups';

    protected static ?int $navigationSort = 2;

    protected string $view = 'filament.pages.manage-site-settings';

    public ?array $data = [];

    public function getSubheading(): ?string
    {
        return 'Changes apply to new page loads right away. To see the banners again yourself, open '.url('/?vp-reset=1').' (it forgets your own choices in that browser).';
    }

    public function mount(): void
    {
        $values = [];
        foreach (VisitorPrompts::FIELDS as $key => $field) {
            $values[$key] = VisitorPrompts::get($key);
        }
        $this->form->fill($values);
    }

    public function form(Schema $schema): Schema
    {
        $on = fn (string $key) => fn (Get $get): bool => (bool) $get($key);

        return $schema
            ->components([
                Section::make('Cookie banner')
                    ->description('A small notice at the bottom of the page. It never blocks the page and disappears once the visitor picks an option.')
                    ->schema([
                        Toggle::make('cookie_banner_enabled')->label('Show the cookie banner')->live()->columnSpanFull(),
                        Textarea::make('cookie_banner_text')->label('Banner text')->rows(2)->columnSpanFull()->visible($on('cookie_banner_enabled')),
                        TextInput::make('cookie_banner_delay')->label('Show after (seconds)')->numeric()->minValue(0)->maxValue(60)
                            ->helperText('0 = straight away. 1–3 seconds feels least intrusive.')->visible($on('cookie_banner_enabled')),
                        Radio::make('cookie_banner_position')->label('Position')->options([
                            'left' => 'Small card, bottom-left',
                            'right' => 'Small card, bottom-right',
                            'bar' => 'Thin bar across the bottom',
                        ])->visible($on('cookie_banner_enabled')),
                        TextInput::make('cookie_accept_label')->label('"Accept" button text')->maxLength(30)->visible($on('cookie_banner_enabled')),
                        TextInput::make('cookie_reject_label')->label('"Essential only" button text')->maxLength(30)->visible($on('cookie_banner_enabled')),
                        Toggle::make('cookie_show_reject')->label('Show the "Essential only" button')
                            ->helperText('Keep this on if you have visitors from the UK/EU: the law expects saying no to be as easy as saying yes.')
                            ->visible($on('cookie_banner_enabled')),
                        TextInput::make('cookie_consent_days')->label('Remember the choice for (days)')->numeric()->minValue(1)->maxValue(730)
                            ->helperText('After this the banner asks again. 365 is common; the EU suggests asking at least every 12 months.')->visible($on('cookie_banner_enabled')),
                        TextInput::make('cookie_privacy_url')->label('Privacy policy link')->maxLength(500)
                            ->helperText('Leave empty to hide the link.')->visible($on('cookie_banner_enabled')),
                        Textarea::make('consent_scripts')->label('Tracking code (runs only after "Accept")')->rows(4)->columnSpanFull()
                            ->helperText('Paste Google Analytics, Facebook Pixel, etc. here instead of anywhere else, so it only runs for visitors who agreed.'),
                    ])
                    ->columns(2),

                Section::make('Deal alerts card (push notifications)')
                    ->description('Visitors first see this small card. The browser\'s own "Allow notifications" popup only appears if they press the yes button, so nobody is surprised by it.')
                    ->schema([
                        Toggle::make('push_enabled')->label('Offer deal alerts to visitors')->live()->columnSpanFull(),
                        TextInput::make('push_prompt_title')->label('Card title')->maxLength(60)->visible($on('push_enabled')),
                        Textarea::make('push_prompt_text')->label('Card text')->rows(2)->maxLength(200)->visible($on('push_enabled')),
                        TextInput::make('push_yes_label')->label('Yes button text')->maxLength(30)->visible($on('push_enabled')),
                        TextInput::make('push_no_label')->label('No button text')->maxLength(30)->visible($on('push_enabled')),
                        TextInput::make('push_welcome_text')->label('Thank-you message after they say yes')->maxLength(120)->columnSpanFull()->visible($on('push_enabled')),
                    ])
                    ->columns(2),

                Section::make('When to show the deal alerts card')
                    ->description('All conditions must be met. The card never appears at the same time as the cookie banner.')
                    ->visible($on('push_enabled'))
                    ->schema([
                        TextInput::make('push_prompt_delay')->label('Wait on the page (seconds)')->numeric()->minValue(0)->maxValue(600)
                            ->helperText('20–40 seconds works well: the visitor has seen what the site offers.'),
                        TextInput::make('push_prompt_min_pages')->label('Only after this many pages viewed')->numeric()->minValue(1)->maxValue(20)
                            ->helperText('2 = never on the very first page they land on.'),
                        TextInput::make('push_prompt_scroll')->label('Only after scrolling this far down (%)')->numeric()->minValue(0)->maxValue(100)
                            ->helperText('0 = no scrolling needed. 30–50 means they are actually reading.'),
                        Radio::make('push_prompt_position')->label('Position (on computers)')->options([
                            'right' => 'Bottom-right',
                            'left' => 'Bottom-left',
                        ]),
                        Toggle::make('push_show_on_mobile')->label('Also ask on phones')
                            ->helperText('Turn off if you want phone screens kept completely clear.'),
                        Toggle::make('push_footer_link')->label('Show "Deal alerts" link in the footer')
                            ->helperText('Lets visitors turn alerts on or off whenever they like.'),
                        TextInput::make('push_snooze_days')->label('After "not now", wait (days)')->numeric()->minValue(1)->maxValue(365),
                        TextInput::make('push_denied_days')->label('After they block notifications, wait (days)')->numeric()->minValue(1)->maxValue(730)
                            ->helperText('Applies when they close the browser popup or press Block.'),
                        Textarea::make('push_exclude_paths')->label('Never ask on these pages')->rows(3)->columnSpanFull()
                            ->placeholder("/privacy-policy\n/contact\n/blog/*")
                            ->helperText('One address per line. * matches anything, e.g. /blog/* = every blog post.'),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        foreach (VisitorPrompts::FIELDS as $key => [$type, $default]) {
            // hidden fields keep their saved value
            if (! array_key_exists($key, $data)) {
                continue;
            }
            $value = $data[$key] ?? ($type === 'bool' ? '0' : '');
            Setting::set($key, $type === 'bool' ? ($value ? '1' : '0') : (string) $value, $type, 'prompts');
        }

        Notification::make()->title('Saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [
            Action::make('save')->label('Save')->submit('save'),
        ];
    }
}
