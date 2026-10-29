<?php

namespace App\Support\Describer;

use App\Filament\Pages\DescriptionWriter;
use App\Models\DescriptionJob;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/** "Write from link" on a description field, plus the bulk version for tables. */
class DescribeAction
{
    public static function make(): Action
    {
        return Action::make('writeFromLink')
            ->label('Write from link')
            ->icon(Heroicon::OutlinedSparkles)
            ->tooltip('Reads the website this item links to and writes a description. Click again for another version.')
            ->action(function (Set $set, $livewire): void {
                $record = method_exists($livewire, 'getRecord') ? $livewire->getRecord() : null;
                $class = $livewire->getModel();
                $item = new $class;

                // use what is in the form now, even if not saved yet
                $state = $livewire->form->getRawState();
                unset($state['description']);
                $item->forceFill(array_intersect_key($state, array_flip(Schema::getColumnListing($item->getTable()))));

                $result = app(Describer::class)->describe($item, null, random_int(1, 1_000_000_000), $record?->getKey());
                if ($result['text'] === '') {
                    Notification::make()->title('Could not write a description')->body($result['note'])->warning()->send();

                    return;
                }
                $set('description', $result['text']);

                Notification::make()
                    ->title('Description written — check it, then save')
                    ->body(($result['site']['ok'] ? 'Read from '.$result['site']['shop'].'. ' : ($result['note'] ? $result['note'].' ' : '')).'Not quite right? Click "Write from link" again for a different version.')
                    ->{$result['site']['ok'] ? 'success' : 'warning'}()
                    ->send();
            });
    }

    public static function bulk(): BulkAction
    {
        return BulkAction::make('writeDescriptions')
            ->label('Write descriptions from links')
            ->icon(Heroicon::OutlinedSparkles)
            ->modalHeading('Write descriptions from links')
            ->modalDescription('Each selected item gets a description written from its title and the website its link goes to. The next page shows progress; keep it open until it finishes. Every change can be undone.')
            ->modalSubmitActionLabel('Start writing')
            ->schema([
                Select::make('length')
                    ->label('Length')
                    ->options(Describer::LENGTHS)
                    ->default(fn () => Setting::get('describer_length', 'medium'))
                    ->required(),
                Toggle::make('overwrite')
                    ->label('Also replace descriptions you have already written')
                    ->helperText('Off: only items with no description, and copies whose description is still the same as the original\'s.')
                    ->default(false),
            ])
            ->action(function (Collection $records, array $data) {
                $batch = (string) Str::uuid();
                foreach ($records as $r) {
                    DescriptionJob::create([
                        'batch' => $batch,
                        'model_type' => $r::class,
                        'model_id' => $r->getKey(),
                        'title' => Str::limit((string) ($r->title ?? $r->name), 250),
                        'length' => $data['length'],
                        'overwrite' => (bool) ($data['overwrite'] ?? false),
                    ]);
                }

                return redirect(DescriptionWriter::getUrl(['batch' => $batch]));
            })
            ->deselectRecordsAfterCompletion();
    }

    public static function copiesFilter(): Filter
    {
        return Filter::make('copies')
            ->label('Copies (duplicates) only')
            ->query(fn (Builder $query) => $query->whereNotNull('copied_from_id'));
    }
}
