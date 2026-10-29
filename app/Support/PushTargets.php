<?php

namespace App\Support;

use App\Models\Category;
use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Page;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductList;
use App\Models\Store;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * The things a push campaign can promote, and how to turn each one into
 * a ready-made notification (title, message, link, image).
 */
class PushTargets
{
    private const MODELS = [
        'store' => [Store::class, 'name', 'Store'],
        'coupon' => [Coupon::class, 'title', 'Coupon'],
        'deal' => [Deal::class, 'title', 'Deal'],
        'product' => [Product::class, 'title', 'Product'],
        'roundup' => [ProductList::class, 'title', 'Best-of roundup'],
        'post' => [Post::class, 'title', 'Blog post'],
        'page' => [Page::class, 'title', 'Page'],
        'category' => [Category::class, 'name', 'Store category'],
        'product_category' => [ProductCategory::class, 'name', 'Product category'],
    ];

    public static function types(): array
    {
        return ['link' => 'Custom link'] + array_map(fn ($m) => $m[2], self::MODELS);
    }

    public static function label(?string $type): string
    {
        return self::types()[$type] ?? 'Custom link';
    }

    public static function find(?string $type, $id): ?Model
    {
        if (! isset(self::MODELS[$type]) || blank($id)) {
            return null;
        }

        return self::MODELS[$type][0]::find($id);
    }

    /** id => label for the searchable picker */
    public static function search(?string $type, ?string $q = null): array
    {
        if (! isset(self::MODELS[$type])) {
            return [];
        }
        [$class, $col] = self::MODELS[$type];

        $query = $class::query()->orderBy($col)->limit(50);
        if (filled($q)) {
            $query->where($col, 'like', '%'.$q.'%');
        }
        if (in_array($type, ['coupon', 'deal'])) {
            $query->with('store');
        }

        return $query->get()->mapWithKeys(fn ($r) => [$r->getKey() => self::optionLabel($type, $r)])->all();
    }

    public static function optionLabel(?string $type, ?Model $r): ?string
    {
        if (! $r || ! isset(self::MODELS[$type])) {
            return null;
        }
        $label = (string) $r->{self::MODELS[$type][1]};
        if (in_array($type, ['coupon', 'deal']) && $r->store) {
            $label .= ' — '.$r->store->name;
        }
        if (isset($r->status) && $r->status !== 'published') {
            $label .= ' ('.$r->status.')';
        }

        return $label;
    }

    /** A ready-made notification for the chosen item; every field can still be edited. */
    public static function suggest(?string $type, $id): ?array
    {
        $r = self::find($type, $id);
        if (! $r) {
            return null;
        }

        $s = match ($type) {
            'store' => [
                'title' => 'New '.$r->name.' coupons & deals',
                'body' => self::text($r->description) ?: 'Fresh, verified codes from '.$r->name.'. Tap to save before they expire.',
                'url' => url('/store/'.$r->slug),
                'image_url' => null,
            ],
            'coupon' => [
                'title' => trim(($r->store?->name ? $r->store->name.': ' : '').$r->title),
                'body' => trim(($r->code ? 'Use code '.$r->code.'. ' : 'No code needed. ').($r->expires_at ? 'Ends '.$r->expires_at->format('M j').'.' : 'Verified today.')),
                'url' => $r->store ? url('/store/'.$r->store->slug) : url('/coupons'),
                'image_url' => null,
            ],
            'deal' => [
                'title' => trim(($r->store?->name ? $r->store->name.': ' : '').$r->title),
                'body' => trim(self::price($r->sale_price, $r->currency, 'Now ').($r->original_price ? ' (was '.self::price($r->original_price, $r->currency).')' : '').($r->expires_at ? ' Ends '.$r->expires_at->format('M j').'.' : '')) ?: (self::text($r->description) ?: 'Limited-time deal. Tap to see it.'),
                'url' => $r->store ? url('/store/'.$r->store->slug) : url('/deals'),
                'image_url' => self::image($r->image),
            ],
            'product' => [
                'title' => $r->short_title ?: $r->title,
                'body' => trim(collect([$r->brand, self::price($r->price, $r->currency), $r->rating ? '★ '.rtrim(rtrim(number_format((float) $r->rating, 1), '0'), '.') : null])->filter()->implode(' · ')) ?: (self::text($r->description) ?: 'One of our top picks. Tap to see why.'),
                'url' => $r->product_category_id && ($c = ProductCategory::find($r->product_category_id)) ? url('/product-category/'.$c->slug) : url('/go/product/'.$r->id),
                'image_url' => self::image($r->image_url),
            ],
            'roundup' => [
                'title' => $r->h1 ?: $r->title,
                'body' => self::text($r->subtitle ?: $r->intro) ?: 'We compared the options so you don’t have to. See our top picks.',
                'url' => url('/'.$r->slug),
                'image_url' => self::image($r->banner),
            ],
            'post' => [
                'title' => $r->title,
                'body' => self::text($r->excerpt ?: $r->body) ?: 'New on the blog. Tap to read.',
                'url' => url('/blog/'.$r->slug),
                'image_url' => self::image($r->cover_image),
            ],
            'page' => [
                'title' => $r->title,
                'body' => self::text($r->meta_description ?: $r->body) ?: 'Tap to have a look.',
                'url' => url('/'.$r->slug),
                'image_url' => null,
            ],
            'category' => [
                'title' => 'Top '.$r->name.' deals',
                'body' => self::text($r->description) ?: 'The best '.$r->name.' coupons and deals, all in one place.',
                'url' => url('/category/'.$r->slug),
                'image_url' => self::image($r->banner),
            ],
            'product_category' => [
                'title' => 'Best '.$r->name,
                'body' => self::text($r->description) ?: 'Our top picks for '.$r->name.'.',
                'url' => url('/product-category/'.$r->slug),
                'image_url' => null,
            ],
        };

        $s['title'] = Str::limit($s['title'], 65, '…');
        $s['body'] = Str::limit($s['body'], 155, '…');

        return $s;
    }

    private static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags((string) $html))));
    }

    private static function price($amount, ?string $currency, string $prefix = ''): string
    {
        if (blank($amount)) {
            return '';
        }
        $symbol = ['USD' => '$', 'GBP' => '£', 'EUR' => '€'][strtoupper((string) ($currency ?: 'USD'))] ?? (strtoupper((string) $currency).' ');

        return $prefix.$symbol.rtrim(rtrim(number_format((float) $amount, 2), '0'), '.');
    }

    private static function image(?string $path): ?string
    {
        if (blank($path)) {
            return null;
        }
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }
        if (Storage::disk('public')->exists($path)) {
            return Storage::disk('public')->url($path);
        }

        return null;
    }
}
