<?php

namespace App\Filament\Resources\PushMessages\Widgets;

use App\Models\PushMessage;
use App\Models\PushSubscription;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PushStats extends BaseWidget
{
    // a few cheap counts: show them with the page instead of loading afterwards
    protected static bool $isLazy = false;

    protected function getStats(): array
    {
        $total = PushSubscription::count();
        $week = PushSubscription::where('created_at', '>=', now()->subDays(7))->count();
        $mobile = PushSubscription::where(function ($q) {
            $q->where('user_agent', 'like', '%Mobile%')->orWhere('user_agent', 'like', '%Android%');
        })->count();

        // new subscribers per day, last 14 days
        $daily = PushSubscription::where('created_at', '>=', now()->subDays(13)->startOfDay())
            ->get(['created_at'])->groupBy(fn ($s) => $s->created_at->toDateString())->map->count();
        $chart = collect(range(13, 0))->map(fn ($d) => $daily[now()->subDays($d)->toDateString()] ?? 0)->all();

        $sent = PushMessage::whereNotNull('sent_at');
        $delivered = (int) (clone $sent)->sum('sent_count');
        $clicks = (int) (clone $sent)->sum('click_count');

        return [
            Stat::make('Subscribers', number_format($total))
                ->description('+'.$week.' in the last 7 days')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->chart($chart)
                ->color('success'),
            Stat::make('Phones / computers', $mobile.' / '.($total - $mobile))
                ->description('Where alerts are turned on'),
            Stat::make('Campaigns sent', (clone $sent)->count())
                ->description(PushMessage::whereNull('sent_at')->whereNotNull('scheduled_at')->count().' scheduled · '
                    .PushMessage::whereNull('sent_at')->whereNull('scheduled_at')->count().' drafts'),
            Stat::make('Clicks', number_format($clicks))
                ->description($delivered ? 'Average click rate '.round($clicks / $delivered * 100, 1).'%' : 'No campaigns delivered yet'),
        ];
    }
}
