<?php

namespace App\Filament\Resources\RefererLinks\Tables;

use App\Models\RefererClick;
use App\Models\RefererLink;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class RefererLinksTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('destinations')->addSelect([
                'clicks_7d' => RefererClick::selectRaw('coalesce(sum(clicks), 0)')
                    ->whereColumn('referer_link_id', 'referer_links.id')
                    ->where('date', '>=', now()->subDays(6)->toDateString()),
            ]))
            ->columns([
                TextColumn::make('id')->label('ID')->sortable(),
                TextColumn::make('code')
                    ->label('Referer URL')
                    ->formatStateUsing(fn (RefererLink $record): string => $record->short_url)
                    ->copyable()
                    ->copyableState(fn (RefererLink $record): string => $record->short_url)
                    ->copyMessage('Short link copied')
                    ->description(fn (RefererLink $record): ?string => $record->name)
                    ->weight('bold')
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn ($w) => $w
                        ->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhereHas('destinations', fn ($d) => $d->where('url', 'like', "%{$search}%")))),
                TextColumn::make('type')
                    ->label('Referer type')
                    ->formatStateUsing(fn (string $state): string => RefererLink::TYPES[$state] ?? $state)
                    ->badge()
                    ->color(fn (string $state): string => ['blank' => 'gray', 'rotational' => 'warning'][$state] ?? 'info')
                    ->description(fn (RefererLink $record): ?string => $record->type === 'rotational'
                        ? Str::before(RefererLink::MODES[$record->rotation_mode ?? 'in_order'] ?? '', ' (')
                        : null)
                    ->sortable(),
                TextColumn::make('destination')
                    ->label('Destination URL')
                    ->state(fn (RefererLink $record): string => $record->type === 'rotational'
                        ? $record->destinations->count().' destinations'
                        : (string) $record->destinations->first()?->url)
                    ->description(fn (RefererLink $record): ?string => $record->type === 'rotational'
                        ? Str::limit($record->destinations->pluck('url')->map(fn ($u) => parse_url($u, PHP_URL_HOST))->implode(' · '), 90)
                        : null)
                    ->limit(90)
                    ->tooltip(fn (RefererLink $record): string => $record->destinations->pluck('url')->implode("\n"))
                    ->wrap(),
                TextColumn::make('click_count')
                    ->label('Clicks')
                    ->numeric()
                    ->sortable()
                    ->description(fn (RefererLink $record): string => (int) $record->clicks_7d.' in 7 days'),
                TextColumn::make('health')
                    ->label('Link check')
                    ->state(fn (RefererLink $record): string => $record->destinations->contains('check_status', 'broken')
                        ? 'Broken'
                        : ($record->destinations->isNotEmpty() && $record->destinations->every(fn ($d) => $d->check_status === 'ok') ? 'OK' : 'Not checked yet'))
                    ->badge()
                    ->color(fn (string $state): string => ['Broken' => 'danger', 'OK' => 'success'][$state] ?? 'gray'),
                IconColumn::make('is_active')->boolean()->label('Active'),
            ])
            ->defaultSort('id', 'desc')
            ->paginated([10, 25, 50, 100])
            ->defaultPaginationPageOption(25)
            ->filters([
                SelectFilter::make('type')->label('Referer type')->options(RefererLink::TYPES),
                TernaryFilter::make('is_active')->label('Active'),
                Filter::make('broken')->label('Broken destinations only')
                    ->query(fn (Builder $query) => $query->whereHas('destinations', fn ($d) => $d->where('check_status', 'broken'))),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
