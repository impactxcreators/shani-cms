<?php

namespace App\Filament\Widgets;

use App\Models\ClickEvent;
use App\Models\Comment;
use App\Models\Coupon;
use App\Models\Post;
use App\Models\Store;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $expiredLive = Coupon::where('status', 'published')
            ->whereNotNull('expires_at')->where('expires_at', '<', now())->count();

        return [
            Stat::make('Clicks — last 7 days', ClickEvent::where('created_at', '>=', now()->subDays(7))->count())
                ->description('Affiliate link clicks')->color('success'),
            Stat::make('Clicks — last 30 days', ClickEvent::where('created_at', '>=', now()->subDays(30))->count()),
            Stat::make('Published coupons', Coupon::where('status', 'published')->count()),
            Stat::make('Coupons expiring (7d)', Coupon::whereBetween('expires_at', [now(), now()->addDays(7)])->count())
                ->color('warning'),
            Stat::make('Expired but still live', $expiredLive)
                ->description($expiredLive > 0 ? 'Needs attention' : 'All good')
                ->color($expiredLive > 0 ? 'danger' : 'gray'),
            Stat::make('Comments pending', Comment::where('status', 'pending')->count())
                ->color('warning'),
            Stat::make('Published posts', Post::where('status', 'published')->count()),
            Stat::make('Active stores', Store::where('status', 'published')->where('is_active', true)->count()),
        ];
    }
}
