<?php

namespace App\Support\Describer;

use App\Models\Coupon;
use App\Models\Deal;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Store;
use Illuminate\Database\Eloquent\Model;

/** Writes a description for a product, store, deal or coupon from its title and the website it links to. */
class Describer
{
    public const LENGTHS = ['short' => 'Short (2 sentences)', 'medium' => 'Medium (2 short paragraphs)', 'long' => 'Long (paragraphs + bullet points)'];

    public function __construct(private ?SiteReader $reader = null, private ?Writer $writer = null)
    {
        $this->reader ??= new SiteReader;
        $this->writer ??= new Writer;
    }

    public static function supports(Model $m): bool
    {
        return $m instanceof Product || $m instanceof Store || $m instanceof Deal || $m instanceof Coupon;
    }

    public function linkFor(Model $m): ?string
    {
        return match (true) {
            $m instanceof Product => $m->affiliate_url,
            // the store's own website first: reading it does not count an affiliate click
            $m instanceof Store => $m->website_url ?: $m->affiliate_url,
            $m instanceof Deal, $m instanceof Coupon => $m->destination_url ?: $m->store?->website_url ?: $m->store?->affiliate_url,
            default => null,
        };
    }

    /**
     * @return array{text: string, site: array, note: ?string}
     */
    public function describe(Model $m, ?string $length = null, ?int $seed = null, ?int $exceptId = null): array
    {
        $length = array_key_exists((string) $length, self::LENGTHS) ? $length : (string) Setting::get('describer_length', 'medium');
        $site = $this->reader->read($this->linkFor($m));
        $facts = $this->facts($m, $site, $length);

        $others = $m->newQuery()
            ->whereKeyNot($exceptId ?? $m->getKey() ?? 0)
            ->whereNotNull('description')->where('description', '!=', '')
            ->latest('id')->limit(300)->pluck('description')->all();

        $seed ??= abs(crc32($facts['title'].'|'.$site['url']));

        return ['text' => $this->writer->write($facts, $seed, $others), 'site' => $site, 'note' => $site['note']];
    }

    public function facts(Model $m, array $site, string $length): array
    {
        $type = match (true) {
            $m instanceof Store => 'store',
            $m instanceof Deal => 'deal',
            $m instanceof Coupon => 'coupon',
            default => 'product',
        };
        $title = Text::clean($m instanceof Store ? $m->name : $m->title);
        $store = $m instanceof Deal || $m instanceof Coupon ? $m->store?->name : null;
        [$subject, $angle] = $type === 'product' ? $this->parseTitle($title) : [$title, null];

        $shop = $type === 'store' ? $title : ($store ?: $site['shop'] ?: ($m instanceof Product ? $m->brand : null) ?: $subject);
        if ($type === 'store') {
            $subject = $title;
        }
        $keep = array_filter([$subject, $shop, $site['shop'], $m instanceof Product ? $m->brand : null]);

        $currency = $m->currency ?? 'USD';
        $symbol = ['USD' => '$', 'GBP' => '£', 'EUR' => '€', 'CAD' => 'CA$', 'AUD' => 'A$'][strtoupper((string) $currency)] ?? '';
        $money = fn ($v) => filled($v) && (float) $v > 0 ? $symbol.rtrim(rtrim(number_format((float) $v, 2), '0'), '.') : null;

        $rating = null;
        if ($m instanceof Product && (float) $m->rating > 0) {
            $r = rtrim(rtrim(number_format((float) $m->rating, 1), '0'), '.');
            $rating = $r.((float) $m->rating > 5 ? '/10' : '/5');
        }

        $discount = null;
        if ($m instanceof Coupon) {
            $v = filled($m->discount_value) ? rtrim(rtrim(number_format((float) $m->discount_value, 2), '0'), '.') : null;
            $discount = match ($m->discount_type) {
                'percent', 'percentage' => $v ? $v.'% off' : null,
                'fixed' => $v ? '$'.$v.' off' : null,
                'free_shipping' => 'free shipping',
                'bogo' => 'a buy-one-get-one deal',
                default => null,
            };
        }

        $oldPrice = $m instanceof Deal ? $money($m->original_price) : null;
        $price = $m instanceof Deal ? $money($m->sale_price) : ($m instanceof Product ? $money($m->price) : null);
        $saving = $m instanceof Deal && (float) $m->original_price > (float) $m->sale_price && (float) $m->sale_price > 0
            ? round((1 - (float) $m->sale_price / (float) $m->original_price) * 100).'%' : null;

        $categories = $m instanceof Store ? $m->categories()->pluck('name')->take(2)->all() : [];
        $lines = fn (string $key) => array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string) Setting::get($key, '')))));

        return [
            'type' => $type,
            'length' => $length,
            'html' => $type === 'product' || $type === 'store',
            'title' => $title,
            'title_soft' => Text::lcfirstSoft($title),
            'subject' => $subject,
            'angle' => $angle ? Text::soften($angle, $keep) : null,
            'audience' => $angle && preg_match('/\b(beginners?|newbies|novices|students?|kids|children|toddlers|babies|teens|families|parents|moms?|dads?|seniors|women|men|gamers|travell?ers|runners|athletes|professionals|creators|designers|developers|photographers|artists|businesses|startups|teams|freelancers|bloggers|families|pets|dogs|cats|owners|homeowners|renters|couples|everyone|anyone|people|users|shoppers|budgets?)$/i', trim($angle)),
            'shop' => $shop,
            'store' => $store ?: $shop,
            'offering' => $site['offering'] && mb_strlen($site['offering']) <= 110 ? SiteReader::tidyOffering($site['offering'], (string) $shop) : null,
            'tagline' => $site['tagline'],
            'tagline_soft' => $site['tagline'] ? Text::lcfirstSoft($site['tagline']) : null,
            'shopSentences' => $site['sentences'],
            'perks' => $site['perks'],
            'features' => $site['features'],
            'customers' => $site['customers'],
            'since' => $site['since'],
            'category' => $m instanceof Product ? $m->category?->name : null,
            'categories' => $categories,
            'highlights' => $m instanceof Product ? array_values(array_filter((array) $m->highlights)) : [],
            'price' => $price,
            'oldPrice' => $oldPrice,
            'saving' => $saving,
            'rating' => $rating,
            'reviews' => $m instanceof Product && $m->review_count ? number_format((int) $m->review_count) : null,
            'code' => $m instanceof Coupon ? $m->code : null,
            'discount' => $discount,
            'verified' => $m instanceof Coupon && $m->is_verified,
            'exclusive' => $m instanceof Coupon && $m->is_exclusive,
            'expires' => ($m instanceof Coupon || $m instanceof Deal) && $m->expires_at ? $m->expires_at->format('F j') : null,
            'custom' => ['openings' => $lines('describer_openings'), 'ctas' => $lines('describer_ctas')],
        ];
    }

    /** "CustomBoxes.io – Best for Custom Kraft Boxes" → ["CustomBoxes.io", "Custom Kraft Boxes"] */
    public function parseTitle(string $title): array
    {
        if (preg_match('/^(.{2,70}?)\s*[–—:|-]\s*(?:the\s+)?(?:best|top pick|great|ideal|perfect|top choice|our pick)\s+(?:overall\s+)?(?:for|choice for|option for|pick for|in)\s+(.{3,120})$/iu', $title, $m)) {
            return [trim($m[1]), trim(preg_replace('/\s*(?:&|and)\s+more$/i', '', $m[2]), " .")];
        }

        return [Text::limit($title, 80), null];
    }
}
