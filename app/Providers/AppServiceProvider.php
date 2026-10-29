<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // ->refererPicker() on a link field adds a button to fill it with one of our /ref/ short links
        \Filament\Forms\Components\TextInput::macro('refererPicker', function () {
            $field = $this->getName();

            return $this->suffixAction(
                \Filament\Actions\Action::make('pickReferer')
                    ->icon(\Filament\Support\Icons\Heroicon::OutlinedLink)
                    ->tooltip('Use a short referer link')
                    ->modalHeading('Use a referer link')
                    ->modalDescription('The field will point to the short link, so changing the short link\'s destination later updates this too.')
                    ->modalSubmitActionLabel('Use this link')
                    ->schema([
                        \Filament\Forms\Components\Select::make('referer_link_id')
                            ->label('Referer link')
                            ->searchable()
                            ->required()
                            ->options(fn () => \App\Models\RefererLink::searchOptions())
                            ->getSearchResultsUsing(fn (?string $search) => \App\Models\RefererLink::searchOptions($search)),
                    ])
                    ->action(function (array $data, \Filament\Schemas\Components\Utilities\Set $set) use ($field) {
                        $set($field, \App\Models\RefererLink::find($data['referer_link_id'])?->short_url);
                    })
            );
        });
    }
}
