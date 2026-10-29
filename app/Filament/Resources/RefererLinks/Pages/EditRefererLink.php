<?php

namespace App\Filament\Resources\RefererLinks\Pages;

use App\Filament\Resources\RefererLinks\RefererLinkResource;
use App\Models\RefererClick;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditRefererLink extends EditRecord
{
    protected static string $resource = RefererLinkResource::class;

    public function getSubheading(): ?string
    {
        $r = $this->getRecord();
        $since = fn (int $days) => (int) RefererClick::where('referer_link_id', $r->id)
            ->where('date', '>=', now()->subDays($days - 1)->toDateString())->sum('clicks');

        return $r->short_url.'  ·  '.$r->click_count.' clicks in total, '.$since(7).' in the last 7 days, '.$since(30).' in the last 30 days'
            .($r->last_clicked_at ? ' · last click '.$r->last_clicked_at->diffForHumans() : '');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('open')
                ->label('Open short link')
                ->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->color('gray')
                ->url(fn (): string => $this->getRecord()->short_url, shouldOpenInNewTab: true),
            DeleteAction::make(),
        ];
    }
}
