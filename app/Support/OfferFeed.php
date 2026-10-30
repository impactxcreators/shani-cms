<?php

namespace App\Support;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Coupons and deals mixed into one sorted, paginated list of offers.
 * Sorting and paging happen in SQL (a UNION of both tables), so big categories stay fast.
 */
class OfferFeed
{
    public const PER_PAGE = 20;

    public const SORTS = ['hot' => 'Hot', 'new' => 'Newest', 'discount' => 'Biggest discount', 'ending' => 'Ending soon'];

    public const TYPES = ['all' => 'All', 'coupons' => 'Coupons', 'deals' => 'Deals'];

    public static function sort(mixed $sort): string
    {
        return is_string($sort) && isset(self::SORTS[$sort]) ? $sort : 'hot';
    }

    public static function type(mixed $type): string
    {
        return is_string($type) && isset(self::TYPES[$type]) ? $type : 'all';
    }

    /**
     * One page of offers from the given stores.
     *
     * @param  Builder  $stores  a Store query selecting only `stores.id`
     * @param  int  $perPage  offers per page (and per "Load more" click), 1-100
     */
    public static function page(Builder $stores, string $type = 'all', string $sort = 'hot', int $perPage = self::PER_PAGE): LengthAwarePaginator
    {
        $parts = [];
        if ($type !== 'deals') {
            $parts[] = Coupon::active()->whereIn('store_id', $stores)->selectRaw(
                "'coupon' as kind, id, created_at, expires_at, used_count as popularity,"
                ." (CASE WHEN is_verified THEN 1 ELSE 0 END + CASE WHEN is_exclusive THEN 1 ELSE 0 END) as boost,"
                ." CASE WHEN discount_type = 'percentage' THEN discount_value ELSE NULL END as pct"
            )->toBase();
        }
        if ($type !== 'coupons') {
            $parts[] = Deal::active()->whereIn('store_id', $stores)->selectRaw(
                "'deal' as kind, id, created_at, expires_at, 0 as popularity,"
                .' (CASE WHEN is_featured THEN 2 ELSE 0 END) as boost,'
                .' CASE WHEN original_price > 0 AND sale_price IS NOT NULL THEN ROUND(100 - sale_price * 100 / original_price) ELSE NULL END as pct'
            )->toBase();
        }

        $union = array_shift($parts);
        foreach ($parts as $part) {
            $union->unionAll($part);
        }

        $query = DB::query()->fromSub($union, 'offers');
        match ($sort) {
            'new' => $query->orderByDesc('created_at'),
            'discount' => $query->orderByRaw('pct IS NULL')->orderByDesc('pct')->orderByDesc('created_at'),
            'ending' => $query->orderByRaw('expires_at IS NULL')->orderBy('expires_at')->orderByDesc('created_at'),
            default => $query->orderByDesc('boost')->orderByDesc('popularity')->orderByDesc('created_at'),
        };
        $page = $query->orderBy('kind')->orderByDesc('id')->paginate(max(1, min($perPage, 100)))->withQueryString();

        // Swap the sorted rows for the real models, keeping the order.
        $rows = $page->getCollection();
        $coupons = Coupon::with('store')->whereIn('id', $rows->where('kind', 'coupon')->pluck('id'))->get()->keyBy('id');
        $deals = Deal::with('store')->whereIn('id', $rows->where('kind', 'deal')->pluck('id'))->get()->keyBy('id');

        return $page->setCollection($rows->map(
            fn ($row) => $row->kind === 'coupon' ? $coupons->get($row->id) : $deals->get($row->id)
        )->filter()->values());
    }

    /** Counts for the filter chips and the intro line. */
    public static function stats(Builder $stores, string $type = 'all'): array
    {
        $coupons = Coupon::active()->whereIn('store_id', $stores);
        $deals = Deal::active()->whereIn('store_id', $stores);
        $withCoupons = $type !== 'deals';
        $withDeals = $type !== 'coupons';

        $storeIds = collect();
        $updated = [];
        if ($withCoupons) {
            $storeIds = $storeIds->merge((clone $coupons)->distinct()->pluck('store_id'));
            $updated[] = (clone $coupons)->max('updated_at');
        }
        if ($withDeals) {
            $storeIds = $storeIds->merge((clone $deals)->distinct()->pluck('store_id'));
            $updated[] = (clone $deals)->max('updated_at');
        }
        $updated = collect($updated)->filter()->max();

        return [
            'coupons' => $withCoupons ? (clone $coupons)->count() : 0,
            'deals' => $withDeals ? (clone $deals)->count() : 0,
            'verified' => $withCoupons ? (clone $coupons)->where('is_verified', true)->count() : 0,
            'stores' => $storeIds->unique()->count(),
            'updated' => $updated ? Carbon::parse($updated) : null,
        ];
    }

    /** "Site has 12 active coupons and 4 deals from 3 Fashion stores. 5 coupons are verified by our team. Updated October 2026." */
    public static function intro(array $stats, string $label = ''): ?string
    {
        $parts = [];
        if ($stats['coupons']) {
            $parts[] = $stats['coupons'].' active '.Str::plural('coupon', $stats['coupons']);
        }
        if ($stats['deals']) {
            $parts[] = $stats['deals'].' '.($parts ? '' : 'active ').Str::plural('deal', $stats['deals']);
        }
        if (! $parts) {
            return null;
        }

        $site = Setting::get('site_name', config('app.name'));
        $stores = $stats['stores'];
        $text = $site.' has '.implode(' and ', $parts).' from '.$stores.' '.trim($label.' '.Str::plural('store', $stores)).'.';
        if ($verified = $stats['verified']) {
            $text .= ' '.$verified.' '.Str::plural('coupon', $verified).' '.($verified === 1 ? 'is' : 'are').' verified by our team.';
        }
        if ($stats['updated']) {
            $text .= ' Updated '.$stats['updated']->format('F Y').'.';
        }

        return str_replace('  ', ' ', $text);
    }
}
