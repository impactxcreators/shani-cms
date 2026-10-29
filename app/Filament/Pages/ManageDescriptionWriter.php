<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use App\Support\Describer\Describer;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ManageDescriptionWriter extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $navigationLabel = 'Description Writer';

    protected static ?string $title = 'Description Writer settings';

    protected static ?int $navigationSort = 3;

    protected string $view = 'filament.pages.manage-description-writer';

    public ?array $data = [];

    // "Try it" box
    public string $previewType = 'product';

    public string $previewTitle = '';

    public string $previewLink = '';

    public string $previewLength = 'medium';

    public ?string $previewResult = null;

    public ?string $previewNote = null;

    public function mount(): void
    {
        $this->form->fill([
            'describer_length' => Setting::get('describer_length', 'medium'),
            'describer_openings' => Setting::get('describer_openings', ''),
            'describer_ctas' => Setting::get('describer_ctas', ''),
        ]);
        $this->previewLength = (string) Setting::get('describer_length', 'medium');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('How descriptions are written')
                    ->description('Free and runs on your own server: it reads the website an item links to and writes from the title, the shop\'s own information and your details (price, rating, highlights, coupon code). No AI and no paid service.')
                    ->schema([
                        Select::make('describer_length')->label('Default length')->options(Describer::LENGTHS)->required(),
                        Textarea::make('describer_openings')
                            ->label('Your own opening lines (optional)')
                            ->rows(4)
                            ->placeholder("Searching for {angle}? {subject} is a brilliant place to start.\n{subject} is a firm favourite with our readers.")
                            ->helperText('One per line. Mixed in with the built-in openings. Placeholders: {subject} (name), {angle} (what it is best for), {shop}, {offering} (what the shop sells), {title}. A line is only used when its placeholders have values.'),
                        Textarea::make('describer_ctas')
                            ->label('Your own closing lines (optional)')
                            ->rows(4)
                            ->placeholder("Don't miss out: see today's price on {shop}.\nTap through to {shop} to learn more.")
                            ->helperText('One per line. Mixed in with the built-in closing lines. Same placeholders as above.'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        Setting::set('describer_length', $data['describer_length'] ?? 'medium', 'string', 'describer');
        Setting::set('describer_openings', (string) ($data['describer_openings'] ?? ''), 'text', 'describer');
        Setting::set('describer_ctas', (string) ($data['describer_ctas'] ?? ''), 'text', 'describer');
        Notification::make()->title('Saved')->success()->send();
    }

    protected function getFormActions(): array
    {
        return [Action::make('save')->label('Save')->submit('save')];
    }

    public function preview(): void
    {
        $this->validate([
            'previewTitle' => ['required', 'string', 'max:255'],
            'previewLink' => ['required', 'string', 'max:2000'],
        ], [], ['previewTitle' => 'title', 'previewLink' => 'link']);

        $item = $this->previewType === 'store'
            ? new Store(['name' => $this->previewTitle, 'website_url' => $this->previewLink])
            : new Product(['title' => $this->previewTitle, 'affiliate_url' => $this->previewLink]);

        $r = app(Describer::class)->describe($item, $this->previewLength, random_int(1, 1_000_000_000));
        $this->previewResult = $r['text'];
        $this->previewNote = $r['site']['ok'] ? 'Read from '.$r['site']['shop'].'.'.($r['note'] ? ' '.$r['note'] : '') : $r['note'];
    }
}
