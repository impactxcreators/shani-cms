<?php

namespace App\Filament\Resources\RefererLinks\Pages;

use App\Filament\Resources\RefererLinks\RefererLinkResource;
use App\Models\RefererLink;
use Filament\Actions\Action;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;

class BulkResults extends Page
{
    protected static string $resource = RefererLinkResource::class;

    protected string $view = 'filament.referers.bulk-results';

    protected static ?string $title = 'Bulk add results';

    public string $batch;

    public function mount(string $batch): void
    {
        $this->batch = $batch;
        abort_unless(RefererLink::where('batch', $batch)->exists(), 404);
    }

    /** every new short link with its destination(s) and any earlier links to the same place */
    public function rows(): array
    {
        $rows = [];
        foreach (RefererLink::where('batch', $this->batch)->with('destinations')->orderBy('id')->get() as $link) {
            foreach ($link->destinations as $d) {
                $rows[] = [
                    'short' => $link->short_url,
                    'type' => RefererLink::TYPES[$link->type] ?? $link->type,
                    'url' => $d->url,
                    'existing' => RefererLink::existingFor($d->url, null, $this->batch)->map->short_url->implode(', '),
                ];
            }
        }

        return $rows;
    }

    public function invalidLines(): array
    {
        return session('referer_batch_invalid.'.$this->batch, []);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('csv')
                ->label('Download CSV')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => response()->streamDownload(function () {
                    $out = fopen('php://output', 'w');
                    fputcsv($out, ['Referer URL', 'Referer type', 'Destination URL', 'Already had short link']);
                    foreach ($this->rows() as $r) {
                        fputcsv($out, [$r['short'], $r['type'], $r['url'], $r['existing']]);
                    }
                    fclose($out);
                }, 'referer-links-'.now()->format('Y-m-d-His').'.csv', ['Content-Type' => 'text/csv'])),
            Action::make('back')
                ->label('Back to all referers')
                ->color('gray')
                ->url(RefererLinkResource::getUrl('index')),
        ];
    }
}
